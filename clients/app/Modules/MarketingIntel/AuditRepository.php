<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_audits` — one AI-assisted Marketing Intelligence audit per row, always
 * scoped to a `projects` row (a project already represents "one client
 * grouping" on the SEO side of this app; reused here rather than inventing a
 * parallel Client entity).
 */
final class AuditRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function create(int $projectId, int $userId, string $title): array
    {
        $now = gmdate('Y-m-d H:i:s');
        $id = $this->db->insert('mi_audits', [
            'client_id' => $projectId,
            'user_id' => $userId,
            'title' => $title,
            'status' => 'collecting',
            'overall_completeness' => 0,
            'overall_confidence' => 0,
            'reachable_tier' => 'none',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->find($id) ?? [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM mi_audits WHERE id = :id', ['id' => $id]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allForProject(int $projectId): array
    {
        return $this->db->all(
            'SELECT * FROM mi_audits WHERE client_id = :client_id ORDER BY created_at DESC',
            ['client_id' => $projectId]
        );
    }

    public function updateProgress(int $auditId, int $completeness, int $confidence, string $reachableTier): void
    {
        $this->db->update(
            'mi_audits',
            [
                'overall_completeness' => $completeness,
                'overall_confidence' => $confidence,
                'reachable_tier' => $reachableTier,
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $auditId]
        );
    }

    public function markCompleted(int $auditId): void
    {
        $this->updateStatus($auditId, 'completed');
    }

    public function updateStatus(int $auditId, string $status): void
    {
        $this->db->update(
            'mi_audits',
            ['status' => $status, 'updated_at' => gmdate('Y-m-d H:i:s')],
            ['id' => $auditId]
        );
    }

    /**
     * @param array<int,string> $names
     */
    public function setKnownEntityNames(int $auditId, array $names): void
    {
        $names = array_values(array_filter(array_map('trim', $names), static fn (string $n): bool => $n !== ''));

        $this->db->update(
            'mi_audits',
            [
                'known_entity_names' => json_encode($names, JSON_THROW_ON_ERROR),
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ],
            ['id' => $auditId]
        );
    }

    /**
     * @return array<int,string>
     */
    public function knownEntityNames(array $audit): array
    {
        $json = $audit['known_entity_names'] ?? null;
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode((string) $json, true);

        return is_array($decoded) ? array_values(array_map('strval', $decoded)) : [];
    }

    /**
     * @param array<int,int> $channelIds
     */
    public function attachChannels(int $auditId, array $channelIds): void
    {
        $now = gmdate('Y-m-d H:i:s');
        foreach ($channelIds as $channelId) {
            $existing = $this->db->one(
                'SELECT id FROM mi_audit_channels WHERE audit_id = :audit_id AND channel_id = :channel_id',
                ['audit_id' => $auditId, 'channel_id' => $channelId]
            );

            if ($existing !== null) {
                continue;
            }

            $this->db->insert('mi_audit_channels', [
                'audit_id' => $auditId,
                'channel_id' => $channelId,
                'selected_at' => $now,
            ]);
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function channelsForAudit(int $auditId): array
    {
        return $this->db->all(
            'SELECT c.* FROM mi_channels c
             INNER JOIN mi_audit_channels ac ON ac.channel_id = c.id
             WHERE ac.audit_id = :audit_id
             ORDER BY c.sort_order ASC',
            ['audit_id' => $auditId]
        );
    }
}
