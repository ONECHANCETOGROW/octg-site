<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_merge_decisions` — records how a KnowledgeFact was resolved when more
 * than one CollectionAttempt produced a competing value for it. Per RNS spec
 * §11: never silently overwrite — every resolution, even an automatic
 * trust-ranking one, is recorded here and surfaced in the Data Provenance
 * appendix (see Reporting views once wired, §14).
 */
final class MergeDecisionRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    /**
     * @param array<int,array{collection_attempt_id:int,value:string,source_trust_tier:int}> $competingValues
     */
    public function record(
        int $knowledgeFactId,
        array $competingValues,
        string $resolutionMethod,
        bool $varianceDetected,
        ?int $resolvedByUserId,
        ?string $notes
    ): int {
        return $this->db->insert('mi_merge_decisions', [
            'knowledge_fact_id' => $knowledgeFactId,
            'competing_values_json' => json_encode($competingValues, JSON_THROW_ON_ERROR),
            'resolution_method' => $resolutionMethod,
            'variance_detected' => $varianceDetected ? 1 : 0,
            'resolved_by_user_id' => $resolvedByUserId,
            'notes' => $notes,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function forKnowledgeFact(int $knowledgeFactId): array
    {
        return $this->db->all(
            'SELECT * FROM mi_merge_decisions WHERE knowledge_fact_id = :id ORDER BY created_at DESC',
            ['id' => $knowledgeFactId]
        );
    }

    /**
     * All merge decisions with variance for an audit — the "needs review"
     * queue referenced throughout the RNS spec (§11, §15).
     *
     * @return array<int,array<string,mixed>>
     */
    public function unresolvedVarianceForAudit(int $auditId): array
    {
        return $this->db->all(
            'SELECT md.* FROM mi_merge_decisions md
             INNER JOIN mi_knowledge_facts kf ON kf.id = md.knowledge_fact_id
             WHERE kf.audit_id = :audit_id AND md.variance_detected = 1
             ORDER BY md.created_at DESC',
            ['audit_id' => $auditId]
        );
    }
}
