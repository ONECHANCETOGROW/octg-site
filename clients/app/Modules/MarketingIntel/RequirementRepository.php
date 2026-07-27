<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_intelligence_requirements` + `mi_requirement_dependencies` — the
 * atomic "thing we need to know" (per RNS spec §2's reframing of the
 * original "Prompt Library" proposal) and the dependency graph between
 * requirements. Prompt/Upload/API adapters (see PromptTemplateRepository,
 * UploadTemplateRepository) are fulfillment methods *for* a requirement,
 * never the primary object themselves.
 */
final class RequirementRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function forChannels(array $channelIds): array
    {
        if ($channelIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($channelIds as $index => $channelId) {
            $key = 'channel' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $channelId;
        }

        return $this->db->all(
            'SELECT * FROM mi_intelligence_requirements
             WHERE channel_id IN (' . implode(', ', $placeholders) . ') AND is_active = 1
             ORDER BY category ASC, sort_order ASC',
            $params
        );
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->one(
            'SELECT * FROM mi_intelligence_requirements WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findByCode(string $code): ?array
    {
        return $this->db->one(
            'SELECT * FROM mi_intelligence_requirements WHERE code = :code',
            ['code' => $code]
        );
    }

    public function upsert(
        int $channelId,
        string $code,
        string $title,
        string $category,
        string $purpose,
        ?string $description,
        bool $isRequired,
        int $confidenceWeight,
        int $sortOrder,
        string $version
    ): int {
        $existing = $this->findByCode($code);
        $now = gmdate('Y-m-d H:i:s');

        $data = [
            'channel_id' => $channelId,
            'title' => $title,
            'category' => $category,
            'purpose' => $purpose,
            'description' => $description,
            'is_required' => $isRequired ? 1 : 0,
            'confidence_weight' => $confidenceWeight,
            'sort_order' => $sortOrder,
            'version' => $version,
            'is_active' => 1,
            'updated_at' => $now,
        ];

        if ($existing === null) {
            $data['code'] = $code;
            $data['created_at'] = $now;

            return $this->db->insert('mi_intelligence_requirements', $data);
        }

        $this->db->update('mi_intelligence_requirements', $data, ['id' => $existing['id']]);

        return (int) $existing['id'];
    }

    /**
     * Records a dependency edge. Idempotent by the (requirement, depends_on)
     * unique key — safe to re-run from a seeder.
     */
    public function upsertDependency(int $requirementId, int $dependsOnRequirementId, string $edgeType): void
    {
        $existing = $this->db->one(
            'SELECT id FROM mi_requirement_dependencies
             WHERE requirement_id = :requirement_id AND depends_on_requirement_id = :depends_on_id',
            ['requirement_id' => $requirementId, 'depends_on_id' => $dependsOnRequirementId]
        );

        if ($existing !== null) {
            $this->db->update(
                'mi_requirement_dependencies',
                ['edge_type' => $edgeType],
                ['id' => $existing['id']]
            );

            return;
        }

        $this->db->insert('mi_requirement_dependencies', [
            'requirement_id' => $requirementId,
            'depends_on_requirement_id' => $dependsOnRequirementId,
            'edge_type' => $edgeType,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * All dependency edges for a set of requirements, in one query — used by
     * DependencyGraph to build the in-memory graph for a cockpit render.
     *
     * @param array<int,int> $requirementIds
     * @return array<int,array{requirement_id:int,depends_on_requirement_id:int,edge_type:string}>
     */
    public function dependenciesFor(array $requirementIds): array
    {
        if ($requirementIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($requirementIds as $index => $id) {
            $key = 'req' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $rows = $this->db->all(
            'SELECT requirement_id, depends_on_requirement_id, edge_type
             FROM mi_requirement_dependencies
             WHERE requirement_id IN (' . implode(', ', $placeholders) . ')',
            $params
        );

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'requirement_id' => (int) $row['requirement_id'],
                'depends_on_requirement_id' => (int) $row['depends_on_requirement_id'],
                'edge_type' => (string) $row['edge_type'],
            ];
        }

        return $result;
    }
}
