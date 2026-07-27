<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_channels` — the registry of marketing channels (Google Ads, SEO, GA4,
 * Meta Ads, ...). Adding a channel is a data row via ChannelSeeder, never a
 * code change (RNS blueprint §17 — additive registration).
 */
final class ChannelRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allActive(): array
    {
        return $this->db->all(
            'SELECT * FROM mi_channels WHERE is_active = 1 ORDER BY sort_order ASC'
        );
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        return $this->db->all('SELECT * FROM mi_channels ORDER BY sort_order ASC');
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findByCode(string $code): ?array
    {
        return $this->db->one('SELECT * FROM mi_channels WHERE code = :code', ['code' => $code]);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM mi_channels WHERE id = :id', ['id' => $id]);
    }

    /**
     * Upserts by unique code — safe to re-run from ChannelSeeder without
     * duplicating rows, same idempotency convention as RuleSeeder on the SEO
     * side of this app.
     */
    public function upsert(
        string $code,
        string $name,
        ?string $description,
        bool $isActive,
        int $sortOrder
    ): void {
        $existing = $this->findByCode($code);
        $now = gmdate('Y-m-d H:i:s');

        if ($existing === null) {
            $this->db->insert('mi_channels', [
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive ? 1 : 0,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return;
        }

        $this->db->update(
            'mi_channels',
            [
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive ? 1 : 0,
                'sort_order' => $sortOrder,
                'updated_at' => $now,
            ],
            ['id' => $existing['id']]
        );
    }
}
