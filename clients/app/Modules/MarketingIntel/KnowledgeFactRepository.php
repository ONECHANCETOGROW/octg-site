<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_knowledge_facts` — one canonical, normalized fact in the Marketing
 * Intelligence Schema (MIS), after merge. This is the *only* table anything
 * downstream of collection (KnowledgeBuilderAdapter and beyond) is allowed
 * to read — per RNS spec §12, nothing downstream should know a fact's
 * original source/method.
 */
final class KnowledgeFactRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    public function upsertFact(
        int $auditId,
        int $requirementId,
        string $entityType,
        string $entityKey,
        string $fieldName,
        string $value,
        ?string $unit,
        ?string $periodStart,
        ?string $periodEnd,
        int $confidence,
        int $sourceCollectionAttemptId
    ): int {
        $now = gmdate('Y-m-d H:i:s');

        $existing = $this->db->one(
            'SELECT id FROM mi_knowledge_facts
             WHERE audit_id = :audit_id AND entity_type = :entity_type
               AND entity_key = :entity_key AND field_name = :field_name',
            [
                'audit_id' => $auditId,
                'entity_type' => $entityType,
                'entity_key' => $entityKey,
                'field_name' => $fieldName,
            ]
        );

        $data = [
            'audit_id' => $auditId,
            'requirement_id' => $requirementId,
            'entity_type' => $entityType,
            'entity_key' => $entityKey,
            'field_name' => $fieldName,
            'value' => $value,
            'unit' => $unit,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'confidence' => max(0, min(100, $confidence)),
            'source_collection_attempt_id' => $sourceCollectionAttemptId,
            'updated_at' => $now,
        ];

        if ($existing === null) {
            $data['created_at'] = $now;

            return $this->db->insert('mi_knowledge_facts', $data);
        }

        $this->db->update('mi_knowledge_facts', $data, ['id' => $existing['id']]);

        return (int) $existing['id'];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allForAudit(int $auditId): array
    {
        return $this->db->all(
            'SELECT * FROM mi_knowledge_facts WHERE audit_id = :audit_id
             ORDER BY entity_type ASC, entity_key ASC, field_name ASC',
            ['audit_id' => $auditId]
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function existingCandidates(
        int $auditId,
        string $entityType,
        string $entityKey,
        string $fieldName
    ): array {
        return $this->db->all(
            'SELECT * FROM mi_knowledge_facts
             WHERE audit_id = :audit_id AND entity_type = :entity_type
               AND entity_key = :entity_key AND field_name = :field_name',
            [
                'audit_id' => $auditId,
                'entity_type' => $entityType,
                'entity_key' => $entityKey,
                'field_name' => $fieldName,
            ]
        );
    }
}
