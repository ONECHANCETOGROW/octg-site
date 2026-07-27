<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Core\DbAdapter;
use App\Modules\MarketingIntel\ChannelRepository;
use App\Modules\MarketingIntel\PromptTemplateRepository;
use App\Modules\MarketingIntel\RequirementRepository;
use App\Modules\MarketingIntel\UploadTemplateRepository;

/**
 * Populates mi_channels / mi_intelligence_requirements /
 * mi_prompt_templates / mi_upload_templates / mi_requirement_dependencies
 * from database/seeds/marketing_intel_catalog.json — the single source of
 * truth for the Prompt Library. This is what "prompts are not hardcoded"
 * means concretely: editing prompt wording, adding a requirement, or
 * changing a dependency is a JSON edit + re-running this seeder, never a
 * PHP code change. Safe to re-run any time (upserts by code, same
 * idempotency convention as RuleSeeder).
 */
final class MarketingIntelSeeder
{
    /**
     * @return array{channels:int,requirements:int,prompts:int,uploads:int,dependencies:int}
     */
    public static function run(DbAdapter $db, string $catalogPath): array
    {
        $json = file_get_contents($catalogPath);
        if ($json === false) {
            throw new \RuntimeException("Unable to read Marketing Intelligence catalog at {$catalogPath}.");
        }

        $catalog = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $channelRepo = new ChannelRepository($db);
        $requirementRepo = new RequirementRepository($db);
        $promptRepo = new PromptTemplateRepository($db);
        $uploadRepo = new UploadTemplateRepository($db);

        $counts = ['channels' => 0, 'requirements' => 0, 'prompts' => 0, 'uploads' => 0, 'dependencies' => 0];

        foreach ($catalog['channels'] as $channel) {
            $channelRepo->upsert(
                $channel['code'],
                $channel['name'],
                $channel['description'] ?? null,
                (bool) $channel['is_active'],
                (int) $channel['sort_order']
            );
            $counts['channels']++;
        }

        $requirementIdByCode = [];

        foreach ($catalog['requirements'] as $requirement) {
            $channelRow = $channelRepo->findByCode($requirement['channel_code']);
            if ($channelRow === null) {
                continue;
            }

            $requirementId = $requirementRepo->upsert(
                (int) $channelRow['id'],
                $requirement['code'],
                $requirement['title'],
                $requirement['category'],
                $requirement['purpose'],
                $requirement['description'] ?? null,
                (bool) $requirement['is_required'],
                (int) $requirement['confidence_weight'],
                (int) $requirement['sort_order'],
                '1.0.0'
            );

            $requirementIdByCode[$requirement['code']] = $requirementId;
            $counts['requirements']++;

            if (isset($requirement['prompt'])) {
                $prompt = $requirement['prompt'];
                $promptRepo->upsert(
                    $requirementId,
                    $prompt['title'],
                    $prompt['target_surface'],
                    $prompt['purpose'],
                    $prompt['description'] ?? null,
                    $prompt['prompt_text'],
                    $prompt['response_format_contract'],
                    $prompt['expected_columns'] ?? [],
                    '1.0.0'
                );
                $counts['prompts']++;
            }

            if (isset($requirement['upload'])) {
                $upload = $requirement['upload'];
                $uploadRepo->upsert(
                    $requirementId,
                    $upload['title'],
                    $upload['accepted_formats'],
                    $upload['expected_columns'] ?? [],
                    $upload['description'] ?? null,
                    '1.0.0'
                );
                $counts['uploads']++;
            }
        }

        foreach ($catalog['dependencies'] ?? [] as $dependency) {
            $requirementId = $requirementIdByCode[$dependency['requirement_code']] ?? null;
            $dependsOnId = $requirementIdByCode[$dependency['depends_on_code']] ?? null;

            if ($requirementId === null || $dependsOnId === null) {
                continue;
            }

            $requirementRepo->upsertDependency($requirementId, $dependsOnId, $dependency['edge_type']);
            $counts['dependencies']++;
        }

        return $counts;
    }
}
