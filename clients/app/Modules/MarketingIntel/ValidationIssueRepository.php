<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_validation_issues` — one problem found by the ValidationEngine (see
 * Validation/ValidationEngine.php) for a given ParsedExtraction. Never
 * silently discarded or auto-resolved — resolution requires an explicit
 * human action recorded here (resolved_by_user_id + resolved_at).
 */
final class ValidationIssueRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    public function create(
        int $parsedExtractionId,
        string $severity,
        string $issueType,
        ?string $fieldName,
        string $message
    ): int {
        return $this->db->insert('mi_validation_issues', [
            'parsed_extraction_id' => $parsedExtractionId,
            'severity' => $severity,
            'issue_type' => $issueType,
            'field_name' => $fieldName,
            'message' => $message,
            'is_resolved' => 0,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function forExtraction(int $parsedExtractionId): array
    {
        return $this->db->all(
            'SELECT * FROM mi_validation_issues WHERE parsed_extraction_id = :id ORDER BY
             FIELD(severity, "critical", "warning", "notice"), id ASC',
            ['id' => $parsedExtractionId]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function unresolvedForExtraction(int $parsedExtractionId): array
    {
        return $this->db->all(
            'SELECT * FROM mi_validation_issues
             WHERE parsed_extraction_id = :id AND is_resolved = 0
             ORDER BY FIELD(severity, "critical", "warning", "notice"), id ASC',
            ['id' => $parsedExtractionId]
        );
    }

    public function resolve(int $issueId, int $resolvedByUserId): void
    {
        $this->db->update(
            'mi_validation_issues',
            [
                'is_resolved' => 1,
                'resolved_by_user_id' => $resolvedByUserId,
                'resolved_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $issueId]
        );
    }
}
