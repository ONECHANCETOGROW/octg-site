<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\DbAdapter;

/**
 * `mi_parsed_extractions` — the structured-JSON result of parsing one
 * CollectionAttempt's raw input, plus per-field confidence. Preserves the
 * original response too (raw_input lives on the CollectionAttempt row this
 * extraction belongs to; the two are always looked up together).
 */
final class ParsedExtractionRepository
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    /**
     * @param array<string,mixed> $structuredData
     * @param array<string,int> $fieldConfidence
     */
    public function create(
        int $collectionAttemptId,
        array $structuredData,
        array $fieldConfidence,
        int $overallConfidence,
        string $parserUsed,
        string $parserVersion = '1.0.0'
    ): int {
        return $this->db->insert('mi_parsed_extractions', [
            'collection_attempt_id' => $collectionAttemptId,
            'structured_json' => json_encode($structuredData, JSON_THROW_ON_ERROR),
            'field_confidence_json' => json_encode($fieldConfidence, JSON_THROW_ON_ERROR),
            'overall_confidence' => max(0, min(100, $overallConfidence)),
            'parser_used' => $parserUsed,
            'parser_version' => $parserVersion,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM mi_parsed_extractions WHERE id = :id', ['id' => $id]);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function forCollectionAttempt(int $collectionAttemptId): ?array
    {
        return $this->db->one(
            'SELECT * FROM mi_parsed_extractions WHERE collection_attempt_id = :collection_attempt_id
             ORDER BY id DESC LIMIT 1',
            ['collection_attempt_id' => $collectionAttemptId]
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function decodeStructured(array $extraction): array
    {
        $decoded = json_decode((string) $extraction['structured_json'], true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string,int>
     */
    public function decodeFieldConfidence(array $extraction): array
    {
        $decoded = json_decode((string) $extraction['field_confidence_json'], true);

        return is_array($decoded) ? $decoded : [];
    }
}
