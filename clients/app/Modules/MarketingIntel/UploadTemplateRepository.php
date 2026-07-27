<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_upload_templates` — the file-upload fulfillment adapter for a
 * Requirement (CSV/Excel/PDF today; the same row shape is what a future
 * ApiMapping table will mirror for Method 3, per RNS spec §18).
 */
final class UploadTemplateRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function forRequirement(int $requirementId): array
    {
        return $this->db->all(
            'SELECT * FROM mi_upload_templates WHERE requirement_id = :requirement_id AND is_active = 1',
            ['requirement_id' => $requirementId]
        );
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM mi_upload_templates WHERE id = :id', ['id' => $id]);
    }

    /**
     * @param array<int,string> $expectedColumns
     */
    public function upsert(
        int $requirementId,
        string $title,
        string $acceptedFormats,
        array $expectedColumns,
        ?string $description,
        string $version
    ): int {
        $now = gmdate('Y-m-d H:i:s');

        $existing = $this->db->one(
            'SELECT id FROM mi_upload_templates WHERE requirement_id = :requirement_id AND title = :title',
            ['requirement_id' => $requirementId, 'title' => $title]
        );

        $data = [
            'requirement_id' => $requirementId,
            'title' => $title,
            'accepted_formats' => $acceptedFormats,
            'expected_columns' => json_encode($expectedColumns, JSON_THROW_ON_ERROR),
            'description' => $description,
            'version' => $version,
            'is_active' => 1,
            'updated_at' => $now,
        ];

        if ($existing === null) {
            $data['created_at'] = $now;

            return $this->db->insert('mi_upload_templates', $data);
        }

        $this->db->update('mi_upload_templates', $data, ['id' => $existing['id']]);

        return (int) $existing['id'];
    }
}
