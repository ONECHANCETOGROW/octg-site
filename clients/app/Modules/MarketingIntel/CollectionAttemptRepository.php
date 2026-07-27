<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_collection_attempts` — one instance of fulfilling a Requirement for a
 * specific Audit, by a specific method. This is the audit-history/versioning
 * record the brief's Database requirement explicitly asks for: every attempt
 * is kept (never overwritten), so re-collecting a requirement adds a new row
 * rather than destroying the previous one.
 */
final class CollectionAttemptRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    public function create(
        int $auditId,
        int $requirementId,
        string $method,
        int $sourceTrustTier,
        ?string $rawInput,
        ?string $originalFilename,
        ?string $originalFilePath,
        int $actorUserId
    ): int {
        $now = gmdate('Y-m-d H:i:s');

        return $this->db->insert('mi_collection_attempts', [
            'audit_id' => $auditId,
            'requirement_id' => $requirementId,
            'method' => $method,
            'source_trust_tier' => $sourceTrustTier,
            'raw_input' => $rawInput,
            'original_filename' => $originalFilename,
            'original_file_path' => $originalFilePath,
            'actor_user_id' => $actorUserId,
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM mi_collection_attempts WHERE id = :id', ['id' => $id]);
    }

    public function markParsed(int $id): void
    {
        $this->db->update(
            'mi_collection_attempts',
            ['status' => 'parsed', 'updated_at' => gmdate('Y-m-d H:i:s')],
            ['id' => $id]
        );
    }

    public function markFailed(int $id, string $reason): void
    {
        $this->db->update(
            'mi_collection_attempts',
            ['status' => 'failed', 'failure_reason' => $reason, 'updated_at' => gmdate('Y-m-d H:i:s')],
            ['id' => $id]
        );
    }

    /**
     * Most recent attempt per requirement for an audit — what the cockpit
     * shows as "current" status, even though older attempts remain in the
     * table for the provenance timeline.
     *
     * @return array<int,array<string,mixed>>
     */
    public function latestPerRequirement(int $auditId): array
    {
        return $this->db->all(
            'SELECT ca.* FROM mi_collection_attempts ca
             INNER JOIN (
                 SELECT requirement_id, MAX(id) AS max_id
                 FROM mi_collection_attempts
                 WHERE audit_id = :audit_id
                 GROUP BY requirement_id
             ) latest ON latest.max_id = ca.id
             WHERE ca.audit_id = :audit_id_2',
            ['audit_id' => $auditId, 'audit_id_2' => $auditId]
        );
    }

    /**
     * Full history for one requirement within an audit — the Data Source
     * Timeline / provenance view.
     *
     * @return array<int,array<string,mixed>>
     */
    public function historyForRequirement(int $auditId, int $requirementId): array
    {
        return $this->db->all(
            'SELECT * FROM mi_collection_attempts
             WHERE audit_id = :audit_id AND requirement_id = :requirement_id
             ORDER BY created_at ASC',
            ['audit_id' => $auditId, 'requirement_id' => $requirementId]
        );
    }

    /**
     * Full chronological timeline for an audit, across every requirement —
     * backs the "Data Source Timeline" screen from RNS spec §4.
     *
     * @return array<int,array<string,mixed>>
     */
    public function timelineForAudit(int $auditId): array
    {
        return $this->db->all(
            'SELECT ca.*, r.title AS requirement_title, r.category AS requirement_category
             FROM mi_collection_attempts ca
             INNER JOIN mi_intelligence_requirements r ON r.id = ca.requirement_id
             WHERE ca.audit_id = :audit_id
             ORDER BY ca.created_at DESC',
            ['audit_id' => $auditId]
        );
    }
}
