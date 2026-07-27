<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\IntelController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\MarketingIntel\Confidence\ConfidenceCalculator;
use App\Modules\MarketingIntel\Merge\MergeEngine;
use App\Modules\MarketingIntel\Merge\SourceTrustRanking;
use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Parsing\ParserFactory;
use App\Modules\MarketingIntel\Validation\ValidationContext;
use App\Modules\MarketingIntel\Validation\ValidationEngine;

/**
 * Handles fulfilling one Requirement for one Audit — the Requirement Detail
 * Drawer's actions (RNS spec §4): show the prompt/upload form, accept a
 * pasted AI response or an uploaded file, run it through
 * Parse -> Validate -> Confidence -> Merge -> KnowledgeFact, and report back
 * what was understood.
 */
final class CollectionController extends IntelController
{
    private const UPLOAD_SUBDIR = 'mi_uploads';

    public function show(Request $request, array $params): void
    {
        $this->requireAuth();

        [$audit, $requirement] = $this->authorizedAuditAndRequirement(
            (int) $params['auditId'],
            (int) $params['requirementId']
        );

        if ($audit === null || $requirement === null) {
            return;
        }

        $promptRepo = new PromptTemplateRepository($this->db);
        $uploadRepo = new UploadTemplateRepository($this->db);
        $collectionRepo = new CollectionAttemptRepository($this->db);
        $extractionRepo = new ParsedExtractionRepository($this->db);
        $issueRepo = new ValidationIssueRepository($this->db);

        $prompts = $promptRepo->forRequirement((int) $requirement['id']);
        $uploadTemplates = $uploadRepo->forRequirement((int) $requirement['id']);
        $history = $collectionRepo->historyForRequirement((int) $audit['id'], (int) $requirement['id']);

        $latestAttempt = $history === [] ? null : $history[count($history) - 1];
        $latestExtraction = null;
        $latestIssues = [];

        if ($latestAttempt !== null) {
            $latestExtraction = $extractionRepo->forCollectionAttempt((int) $latestAttempt['id']);
            if ($latestExtraction !== null) {
                $latestIssues = $issueRepo->forExtraction((int) $latestExtraction['id']);
            }
        }

        $renderedPrompts = [];
        foreach ($prompts as $prompt) {
            $variables = [
                'account_name' => (string) ($audit['title'] ?? ''),
                'date_range' => 'the last 30 days',
            ];

            $renderedPrompts[] = [
                'template' => $prompt,
                'rendered_text' => $promptRepo->render((string) $prompt['prompt_text'], $variables)
                    . "\n\n" . $promptRepo->render((string) $prompt['response_format_contract'], $variables),
            ];
        }

        $this->render('MarketingIntel/views/requirement_detail', [
            'audit' => $audit,
            'requirement' => $requirement,
            'renderedPrompts' => $renderedPrompts,
            'uploadTemplates' => $uploadTemplates,
            'history' => $history,
            'latestAttempt' => $latestAttempt,
            'latestExtraction' => $latestExtraction,
            'latestIssues' => $latestIssues,
            'error' => Session::getFlash('error'),
        ]);
    }

    public function collectText(Request $request, array $params): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail($request);

        [$audit, $requirement] = $this->authorizedAuditAndRequirement(
            (int) $params['auditId'],
            (int) $params['requirementId']
        );

        if ($audit === null || $requirement === null) {
            return;
        }

        $rawText = trim((string) $request->post('response_text', ''));
        $rawText = \App\Services\AIParser\MarkdownTableSanitizer::sanitize($rawText);
        
        $method = (string) $request->post('method', 'ai_assistant');
        if (!in_array($method, ['ai_assistant', 'manual', 'upload_csv'], true)) {
            $method = 'ai_assistant';
        }

        if ($rawText === '') {
            Session::flash('error', 'Paste a response before submitting.');
            $this->redirectToRequirement((int) $audit['id'], (int) $requirement['id']);

            return;
        }

        $parserFactory = new ParserFactory();
        $parser = $parserFactory->textParserFor($method);
        if ($parser === null) {
            Session::flash('error', "No parser available for method \"{$method}\".");
            $this->redirectToRequirement((int) $audit['id'], (int) $requirement['id']);

            return;
        }

        $expectedColumns = $this->expectedColumnsFor((int) $requirement['id'], $method);
        $payload = $parser->parseText($rawText, $expectedColumns);

        $this->processCollection(
            $audit,
            $requirement,
            $method,
            $rawText,
            null,
            null,
            $parser->code(),
            $payload,
            $expectedColumns
        );

        Response::redirect("/audits/{$audit['id']}/requirements/{$requirement['id']}");
    }

    public function collectFile(Request $request, array $params): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail($request);

        [$audit, $requirement] = $this->authorizedAuditAndRequirement(
            (int) $params['auditId'],
            (int) $params['requirementId']
        );

        if ($audit === null || $requirement === null) {
            return;
        }

        $file = $request->file('upload');
        if ($file === null || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Choose a file before uploading.');
            $this->redirectToRequirement((int) $audit['id'], (int) $requirement['id']);

            return;
        }

        $originalName = (string) ($file['name'] ?? 'upload');
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        $method = match ($extension) {
            'csv' => 'upload_csv',
            'xlsx', 'xls' => 'upload_excel',
            'pdf' => 'upload_pdf',
            default => null,
        };

        if ($method === null) {
            Session::flash('error', 'Unsupported file type — upload a .csv, .xlsx, or .pdf file.');
            $this->redirectToRequirement((int) $audit['id'], (int) $requirement['id']);

            return;
        }

        $storedPath = $this->storeUploadedFile((int) $audit['id'], (string) $file['tmp_name'], $originalName);
        if ($storedPath === null) {
            Session::flash('error', 'Could not save the uploaded file.');
            $this->redirectToRequirement((int) $audit['id'], (int) $requirement['id']);

            return;
        }

        $expectedColumns = $this->expectedColumnsFor((int) $requirement['id'], $method);
        $parserFactory = new ParserFactory();

        if ($method === 'upload_csv') {
            // CSV is simple enough to read as text even when it arrives as
            // a file — reuse the text-based CsvParser rather than a
            // separate file-based implementation.
            $csvText = file_get_contents($storedPath['absolute']);
            $parser = $parserFactory->textParserFor('upload_csv');
            $payload = $parser instanceof \App\Modules\MarketingIntel\Parsing\TextParserInterface
                ? $parser->parseText((string) $csvText, $expectedColumns)
                : ParsedPayload::empty('csv_parser_unavailable');
            $parserCode = 'csv_parser';
        } else {
            $parser = $parserFactory->fileParserFor($method);
            $payload = $parser?->parseFile($storedPath['absolute'], $expectedColumns)
                ?? ParsedPayload::empty('no_parser_available');
            $parserCode = $parser?->code() ?? 'none';
        }

        $this->processCollection(
            $audit,
            $requirement,
            $method,
            null,
            $originalName,
            $storedPath['relative'],
            $parserCode,
            $payload,
            $expectedColumns
        );

        Response::redirect("/audits/{$audit['id']}/requirements/{$requirement['id']}");
    }

    public function resolveIssue(Request $request, array $params): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail($request);

        [$audit, $requirement] = $this->authorizedAuditAndRequirement(
            (int) $params['auditId'],
            (int) $params['requirementId']
        );

        if ($audit === null || $requirement === null) {
            return;
        }

        $issueRepo = new ValidationIssueRepository($this->db);
        $issueRepo->resolve((int) $params['issueId'], (int) $this->userId());

        Response::redirect("/audits/{$audit['id']}/requirements/{$requirement['id']}");
    }

    /**
     * @param array<string,mixed> $audit
     * @param array<string,mixed> $requirement
     * @param array<int,string> $expectedColumns
     */
    private function processCollection(
        array $audit,
        array $requirement,
        string $method,
        ?string $rawText,
        ?string $originalFilename,
        ?string $storedRelativePath,
        string $parserCode,
        ParsedPayload $payload,
        array $expectedColumns
    ): void {
        $trustRanking = new SourceTrustRanking();
        $usedFallback = in_array('fallback_key_value_used', $payload->structuralWarnings, true);
        $sourceTrustTier = $trustRanking->tierFor($method, $usedFallback);

        $collectionRepo = new CollectionAttemptRepository($this->db);
        $attemptId = $collectionRepo->create(
            (int) $audit['id'],
            (int) $requirement['id'],
            $method,
            $sourceTrustTier,
            $rawText,
            $originalFilename,
            $storedRelativePath,
            (int) $this->userId()
        );

        if ($payload->isEmpty() && $payload->structuralWarnings === []) {
            $collectionRepo->markFailed($attemptId, 'unrecognized_input');

            return;
        }

        $auditRepo = new AuditRepository($this->db);
        $auditRow = $auditRepo->find((int) $audit['id']);
        $knownNames = $auditRow !== null ? $auditRepo->knownEntityNames($auditRow) : [];

        $validationEngine = new ValidationEngine();
        $context = new ValidationContext($rawText ?? ($originalFilename ?? ''), $expectedColumns, $knownNames);
        $findings = $validationEngine->evaluate($payload, $context);

        $confidenceCalc = new ConfidenceCalculator($validationEngine);
        $baseConfidence = $confidenceCalc->extractionBaseConfidence($payload);
        $finalConfidence = $confidenceCalc->requirementConfidence($baseConfidence, $findings);

        $fieldConfidence = $payload->fieldConfidence;

        $extractionRepo = new ParsedExtractionRepository($this->db);
        $extractionId = $extractionRepo->create(
            $attemptId,
            $payload->toStructuredArray(),
            $fieldConfidence,
            $finalConfidence,
            $parserCode
        );

        $issueRepo = new ValidationIssueRepository($this->db);
        foreach ($findings as $finding) {
            $issueRepo->create($extractionId, $finding->severity, $finding->issueType, $finding->fieldName, $finding->message);
        }

        $hasCriticalStructuralFailure = array_filter(
            $findings,
            static fn ($f) => $f->issueType === 'structural_format_failure'
        );

        if ($hasCriticalStructuralFailure !== []) {
            $collectionRepo->markFailed($attemptId, 'structural_format_failure');

            return;
        }

        $collectionRepo->markParsed($attemptId);

        $factRepo = new KnowledgeFactRepository($this->db);
        $decisionRepo = new MergeDecisionRepository($this->db);
        $mergeEngine = new MergeEngine($factRepo, $decisionRepo);
        $mergeEngine->merge(
            (int) $audit['id'],
            (int) $requirement['id'],
            $payload,
            $finalConfidence,
            $attemptId,
            $sourceTrustTier
        );
    }

    /**
     * @return array<int,string>
     */
    private function expectedColumnsFor(int $requirementId, string $method): array
    {
        if (in_array($method, ['ai_assistant', 'manual'], true)) {
            $promptRepo = new PromptTemplateRepository($this->db);
            $prompts = $promptRepo->forRequirement($requirementId);
            if ($prompts === []) {
                return [];
            }

            $decoded = json_decode((string) $prompts[0]['expected_columns'], true);

            return is_array($decoded) ? $decoded : [];
        }

        $uploadRepo = new UploadTemplateRepository($this->db);
        $templates = $uploadRepo->forRequirement($requirementId);
        if ($templates === []) {
            return [];
        }

        $decoded = json_decode((string) $templates[0]['expected_columns'], true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array{absolute:string,relative:string}|null
     */
    private function storeUploadedFile(int $auditId, string $tmpPath, string $originalName): ?array
    {
        $storageRoot = dirname(__DIR__, 3) . '/storage/' . self::UPLOAD_SUBDIR . '/' . $auditId;

        if (!is_dir($storageRoot) && !mkdir($storageRoot, 0750, true) && !is_dir($storageRoot)) {
            return null;
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?? 'upload';
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '_' . $safeName;
        $destination = $storageRoot . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $destination) && !@rename($tmpPath, $destination)) {
            return null;
        }

        return [
            'absolute' => $destination,
            'relative' => self::UPLOAD_SUBDIR . '/' . $auditId . '/' . $filename,
        ];
    }

    private function redirectToRequirement(int $auditId, int $requirementId): void
    {
        Response::redirect("/audits/{$auditId}/requirements/{$requirementId}");
    }

    /**
     * @return array{0:array<string,mixed>|null,1:array<string,mixed>|null}
     */
    private function authorizedAuditAndRequirement(int $auditId, int $requirementId): array
    {
        $auditRepo = new AuditRepository($this->db);
        $audit = $auditRepo->find($auditId);

        if ($audit === null || (int) $audit['user_id'] !== (int) $this->userId()) {
            Response::notFound('Audit not found.');

            return [null, null];
        }

        $requirementRepo = new RequirementRepository($this->db);
        $requirement = $requirementRepo->find($requirementId);

        if ($requirement === null) {
            Response::notFound('Requirement not found.');

            return [null, null];
        }

        return [$audit, $requirement];
    }

    // sanitizeMarkdownTable() used to live here as a private method; it's
    // now App\Services\AIParser\MarkdownTableSanitizer::sanitize(), shared
    // with the newer AI Data Collection pipeline (NLParser) so both paths
    // fix the same "squished single-line table" problem the same way
    // instead of maintaining two copies of the same logic.
}

