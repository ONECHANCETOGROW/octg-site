<?php

declare(strict_types=1);

namespace App\Modules\Shared;

/**
 * Extracts the @type value(s) out of decoded JSON-LD blocks. Shared
 * because both App\Modules\SeoEngine\PageContext (per-page rule checks)
 * and the Website Intelligence Engine's HealthScorer (sitewide Local SEO
 * Readiness / Schema Health) need the exact same extraction — see
 * CODING_STANDARDS.md "no duplicated logic".
 */
final class SchemaTypes
{
    /**
     * @param array<int,array<string,mixed>> $schemaBlocks decoded JSON-LD blocks
     * @return array<int,string> lowercased @type values across all blocks
     */
    public static function fromBlocks(array $schemaBlocks): array
    {
        $types = [];

        foreach ($schemaBlocks as $block) {
            $type = $block['@type'] ?? null;

            if (is_string($type)) {
                $types[] = strtolower($type);
            } elseif (is_array($type)) {
                foreach ($type as $t) {
                    if (is_string($t)) {
                        $types[] = strtolower($t);
                    }
                }
            }
        }

        return $types;
    }
}
