<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Merge;

use App\Modules\MarketingIntel\KnowledgeFactRepository;
use App\Modules\MarketingIntel\MergeDecisionRepository;
use App\Modules\MarketingIntel\Parsing\ParsedPayload;

/**
 * Turns a freshly-parsed ParsedPayload into KnowledgeFact rows, resolving
 * any conflict against facts already recorded for this audit — RNS spec
 * §11. Never silently overwrites a disagreeing value: agreement raises
 * confidence (corroboration), disagreement is resolved by source-trust
 * ranking but always recorded as a MergeDecision with variance_detected =
 * true, so it surfaces in the Data Provenance appendix rather than quietly
 * disappearing.
 */
final class MergeEngine
{
    private const NUMERIC_AGREEMENT_TOLERANCE_PERCENT = 5.0;

    public function __construct(
        private readonly KnowledgeFactRepository $facts,
        private readonly MergeDecisionRepository $decisions
    ) {
    }

    public function merge(
        int $auditId,
        int $requirementId,
        ParsedPayload $payload,
        int $extractionConfidence,
        int $collectionAttemptId,
        int $sourceTrustTier
    ): void {
        foreach ($this->candidateFacts($payload) as $candidate) {
            $this->mergeOneFact(
                $auditId,
                $requirementId,
                $candidate['entity_type'],
                $candidate['entity_key'],
                $candidate['field_name'],
                $candidate['value'],
                $extractionConfidence,
                $collectionAttemptId,
                $sourceTrustTier
            );
        }
    }

    /**
     * @return array<int,array{entity_type:string,entity_key:string,field_name:string,value:string}>
     */
    private function candidateFacts(ParsedPayload $payload): array
    {
        $candidates = [];

        foreach ($payload->rows as $row) {
            if ($row === []) {
                continue;
            }

            // The first populated column is treated as the entity's
            // identifying key (typically "Campaign", "Keyword", "Location",
            // "Landing Page" — whatever the prompt's first expected column
            // was) — every other column becomes one fact about that entity.
            $columns = array_keys($row);
            $entityKey = trim($row[$columns[0]] ?? '');
            if ($entityKey === '') {
                continue;
            }

            for ($i = 1; $i < count($columns); $i++) {
                $fieldName = $columns[$i];
                $value = trim($row[$fieldName] ?? '');
                if ($value === '') {
                    continue;
                }

                $candidates[] = [
                    'entity_type' => $columns[0],
                    'entity_key' => $entityKey,
                    'field_name' => $fieldName,
                    'value' => $value,
                ];
            }
        }

        foreach ($payload->scalars as $key => $value) {
            if (trim($value) === '') {
                continue;
            }

            $candidates[] = [
                'entity_type' => 'Account',
                'entity_key' => 'account_total',
                'field_name' => $key,
                'value' => trim($value),
            ];
        }

        return $candidates;
    }

    private function mergeOneFact(
        int $auditId,
        int $requirementId,
        string $entityType,
        string $entityKey,
        string $fieldName,
        string $value,
        int $confidence,
        int $collectionAttemptId,
        int $sourceTrustTier
    ): void {
        $existingCandidates = $this->facts->existingCandidates($auditId, $entityType, $entityKey, $fieldName);

        if ($existingCandidates === []) {
            $factId = $this->facts->upsertFact(
                $auditId,
                $requirementId,
                $entityType,
                $entityKey,
                $fieldName,
                $value,
                null,
                null,
                null,
                $confidence,
                $collectionAttemptId
            );

            $this->decisions->record(
                $factId,
                [['collection_attempt_id' => $collectionAttemptId, 'value' => $value, 'source_trust_tier' => $sourceTrustTier]],
                'single_source',
                false,
                null,
                null
            );

            return;
        }

        $existing = $existingCandidates[0];
        $existingValue = (string) $existing['value'];
        $existingConfidence = (int) $existing['confidence'];

        if ($this->valuesAgree($existingValue, $value)) {
            // Corroboration: raise confidence above either individual
            // source's baseline, per RNS spec §11.
            $boostedConfidence = min(100, max($existingConfidence, $confidence) + 8);

            $factId = $this->facts->upsertFact(
                $auditId,
                $requirementId,
                $entityType,
                $entityKey,
                $fieldName,
                $value,
                null,
                null,
                null,
                $boostedConfidence,
                $collectionAttemptId
            );

            $this->decisions->record(
                $factId,
                [
                    ['collection_attempt_id' => (int) $existing['source_collection_attempt_id'], 'value' => $existingValue, 'source_trust_tier' => $sourceTrustTier],
                    ['collection_attempt_id' => $collectionAttemptId, 'value' => $value, 'source_trust_tier' => $sourceTrustTier],
                ],
                'corroborated',
                false,
                null,
                'Independent sources agreed within tolerance; confidence boosted.'
            );

            return;
        }

        // Disagreement beyond tolerance: resolve by trust ranking, but
        // record the conflict rather than silently picking a winner.
        $winningValue = $sourceTrustTier >= $existingConfidence ? $value : $existingValue;
        $winningConfidence = max($existingConfidence, $confidence);

        $factId = $this->facts->upsertFact(
            $auditId,
            $requirementId,
            $entityType,
            $entityKey,
            $fieldName,
            $winningValue,
            null,
            null,
            null,
            $winningConfidence,
            $collectionAttemptId
        );

        $this->decisions->record(
            $factId,
            [
                ['collection_attempt_id' => (int) $existing['source_collection_attempt_id'], 'value' => $existingValue, 'source_trust_tier' => $sourceTrustTier],
                ['collection_attempt_id' => $collectionAttemptId, 'value' => $value, 'source_trust_tier' => $sourceTrustTier],
            ],
            'trust_ranking',
            true,
            null,
            'Conflicting values from different sources — resolved by source-trust ranking. Human review recommended.'
        );
    }

    private function valuesAgree(string $a, string $b): bool
    {
        if (strcasecmp(trim($a), trim($b)) === 0) {
            return true;
        }

        $numericA = $this->toNumber($a);
        $numericB = $this->toNumber($b);

        if ($numericA === null || $numericB === null) {
            return false;
        }

        if ($numericA === 0.0 && $numericB === 0.0) {
            return true;
        }

        $base = max(abs($numericA), abs($numericB));
        if ($base === 0.0) {
            return true;
        }

        $diffPercent = abs($numericA - $numericB) / $base * 100;

        return $diffPercent <= self::NUMERIC_AGREEMENT_TOLERANCE_PERCENT;
    }

    private function toNumber(string $value): ?float
    {
        $cleaned = trim(str_replace(['$', ',', '%', '€', '£'], '', $value));

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}
