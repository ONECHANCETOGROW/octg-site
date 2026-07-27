<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_prompt_templates` — the AI-Assistant fulfillment adapter for a
 * Requirement. Every field here is data, editable via RequirementSeeder's
 * JSON catalog — never hardcoded PHP logic, per the brief's explicit "no
 * hardcoded prompts" requirement.
 */
final class PromptTemplateRepository
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
            'SELECT * FROM mi_prompt_templates WHERE requirement_id = :requirement_id AND is_active = 1
             ORDER BY id ASC',
            ['requirement_id' => $requirementId]
        );
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM mi_prompt_templates WHERE id = :id', ['id' => $id]);
    }

    /**
     * @param array<int,string> $expectedColumns
     */
    public function upsert(
        int $requirementId,
        string $title,
        string $targetSurface,
        string $purpose,
        ?string $description,
        string $promptText,
        string $responseFormatContract,
        array $expectedColumns,
        string $version
    ): int {
        $now = gmdate('Y-m-d H:i:s');

        // A requirement can, in principle, have more than one prompt
        // template (e.g. a "short" and "detailed" variant), so upsert keys
        // on (requirement_id, title) rather than a single global unique code.
        $existing = $this->db->one(
            'SELECT id FROM mi_prompt_templates WHERE requirement_id = :requirement_id AND title = :title',
            ['requirement_id' => $requirementId, 'title' => $title]
        );

        $data = [
            'requirement_id' => $requirementId,
            'title' => $title,
            'target_surface' => $targetSurface,
            'purpose' => $purpose,
            'description' => $description,
            'prompt_text' => $promptText,
            'response_format_contract' => $responseFormatContract,
            'expected_columns' => json_encode($expectedColumns, JSON_THROW_ON_ERROR),
            'version' => $version,
            'is_active' => 1,
            'updated_at' => $now,
        ];

        if ($existing === null) {
            $data['created_at'] = $now;

            return $this->db->insert('mi_prompt_templates', $data);
        }

        $this->db->update('mi_prompt_templates', $data, ['id' => $existing['id']]);

        return (int) $existing['id'];
    }

    /**
     * Renders `{{variable}}` placeholders in a prompt's text/format-contract
     * with real values (e.g. account name, date range) supplied by the
     * cockpit at collection time.
     *
     * @param array<string,string> $variables
     */
    public function render(string $template, array $variables): string
    {
        $rendered = $template;
        foreach ($variables as $key => $value) {
            $rendered = str_replace('{{' . $key . '}}', $value, $rendered);
        }

        return $rendered;
    }
}
