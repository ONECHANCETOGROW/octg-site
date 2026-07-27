<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Bridge;

/**
 * Converts KnowledgeBuilderAdapter::factsForAudit()'s generic
 * entity_type -> entity_key -> field_name -> {value} shape into the exact
 * format intelligence_engine/schemas/knowledge.schema.json requires
 * (metadata / statistics / entities.{campaigns,searchTerms,...}), so
 * AI-collected audits produce REAL scored intelligence through the same
 * Rule/Scoring/Opportunity/Recommendation/Executive-Summary pipeline that
 * CSV/Excel-uploaded audits already use -- not a separate, fake path.
 *
 * FIXES A REAL BUG: IntelAuditController::process() previously wrote
 * {"knowledge": $facts} directly to 03-knowledge/knowledge.json. The Node
 * Rule Engine reads `knowledgeData.entities || {}` -- since that key never
 * existed in the old output, EVERY AI-collected audit silently evaluated
 * zero rules. The pipeline exited 0 (looked successful) and produced an
 * empty/trivial rule_results.json, so scoring/opportunities/
 * recommendations for AI-collected data were effectively blank. This class
 * is the fix.
 *
 * Classification strategy: instead of trusting the free-text entity_type
 * label a strategist's pasted-in AI response happened to produce
 * ("Campaign", "Campaigns", "campaign_name", ...), each (entity_type,
 * entity_key) group is classified by which column shape it matches --
 * exactly the same `required_columns` matching
 * intelligence_engine/modules/02_knowledge/index.js already uses for
 * CSV/Excel rows (see identifyEntity() there). This is what "the
 * Intelligence Engine should never care where the data originated" (the
 * brief's Data Source Manager requirement) means concretely: one
 * classification rule, reused by every source.
 *
 * FIXES A SECOND REAL BUG (found after the classification fallback above
 * started actually classifying campaign/keyword/search-term rows for the
 * first time): total_spend/total_conversions/total_clicks used to be
 * summed from EVERY row that had those fields, with no regard for the
 * fact that an account-level KPI row, its per-campaign breakdown, its
 * per-keyword breakdown, and its per-search-term breakdown all describe
 * the SAME spend at different granularities -- not four separate pots of
 * money to add together. Worse, classified rows were summed into
 * statistics twice (once in the classified branch, once again in an
 * unconditional pass right after it). The result: a $2,488 account
 * silently reported as $10,089 once campaigns/keywords/search-terms
 * started classifying correctly, which is also why the scorecard read
 * 100 -- it was scoring numbers that were roughly 4x real. See
 * resolveStatistics() below for the fix: exactly one source of truth,
 * never summed twice.
 */
final class KnowledgeContractBuilder
{
    private const NUMERIC_FIELDS = ['spend', 'conversions', 'clicks', 'impressions'];

    /**
     * @param array<string,array<string,array<string,array{value:string,unit:?string,confidence:int}>>> $factsForAudit
     * @param array<string,mixed> $pluginManifest decoded plugins/{plugin}/plugin.json
     * @return array<string,mixed> matches intelligence_engine/schemas/knowledge.schema.json
     */
    public function build(array $factsForAudit, array $pluginManifest, int $clientId, int $auditId): array
    {
        $headerMap = $pluginManifest['header_normalization'] ?? [];
        $entityDefs = $pluginManifest['entities'] ?? [];

        $entities = [
            'campaigns' => [], 'searchTerms' => [], 'keywords' => [], 'devices' => [],
            'locations' => [], 'conversions' => [], 'landingPages' => [], 'tracking' => [],
            'budget' => [], 'audiences' => [], 'extensions' => [],
            // These three are NOT column-shaped entities that classify()
            // can recognize -- they're the AI advisor's own free-text
            // recommendations/opportunities/executive-summary answers,
            // written by AuditMerger under exactly these entity_type
            // names. intelligence_engine's 05_opportunities,
            // 06_recommendations, and 07_executive_summary modules already
            // look for knowledge.entities.ai_opportunities /
            // ai_recommendations / ai_executive_summary and merge them
            // into the final report -- that merge code has existed all
            // along. Before this fix, these three groups fell through
            // classify() (no campaign_name/search_term/keyword/etc. to
            // match against) and were silently discarded as
            // "unclassified", so the AI's actual recommendations,
            // opportunities, and executive summary never reached the
            // report at all, no matter how good the advisor's answer was.
            'ai_recommendations' => [], 'ai_opportunities' => [], 'ai_executive_summary' => [],
        ];

        $accountLevelStats = null;
        $unclassifiedRows = 0;

        foreach ($factsForAudit as $entityType => $entityKeyMap) {
            foreach ($entityKeyMap as $fields) {
                $row = [];
                foreach ($fields as $fieldName => $fact) {
                    $normalized = $this->normalizeField((string) $fieldName, $headerMap);
                    $row[$normalized] = $this->cleanValue($normalized, (string) $fact['value']);
                }

                if ($row === []) {
                    continue;
                }

                // AuditMerger writes the account-level KPI answer as
                // entity_type "statistics" (entity_key "kpis") -- that IS
                // the account total the advisor/CSV reported, not a
                // breakdown to be added on top of anything else. It's the
                // single most trustworthy source for the report's
                // top-line numbers, so it's captured separately and never
                // mixed into the per-row entity classification below.
                if ($entityType === 'statistics') {
                    $accountLevelStats = $row;
                    continue;
                }

                // Pass the three AI free-text groups straight through by
                // their already-unambiguous entity_type, bypassing
                // classify() entirely -- see the $entities initializer
                // above for why they'd never match a column-shape rule.
                if (in_array($entityType, ['ai_recommendations', 'ai_opportunities', 'ai_executive_summary'], true)) {
                    $entities[$entityType][] = $row;
                    continue;
                }

                $category = $this->classify($row, $entityDefs);

                if ($category !== null) {
                    $entities[$category][] = $row;
                } else {
                    $unclassifiedRows++;
                }
            }
        }

        $statistics = $this->resolveStatistics($accountLevelStats, $entities['campaigns']);

        return [
            'metadata' => [
                'contract_version' => '1.0',
                'engine_version' => '1.0',
                'plugin' => $pluginManifest['plugin_name'] ?? 'google_ads',
                'plugin_version' => $pluginManifest['version'] ?? '1.0',
                'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'client_id' => (string) $clientId,
                'audit_id' => (string) $auditId,
                'source_files' => ['ai_data_collection'],
                // Not part of the schema's required fields but kept for
                // debugging -- how many fact groups didn't match any
                // known entity shape, worth checking if scoring looks thin.
                'unclassified_fact_groups' => $unclassifiedRows,
            ],
            'statistics' => $statistics,
            'entities' => $entities,
        ];
    }

    /**
     * Exactly one source feeds total_spend/total_conversions/total_clicks/
     * total_impressions -- never a sum of several overlapping sources.
     *
     * Preferred: the account-level "kpis" row, when the advisor/CSV
     * provided one -- it's a direct statement of the real total, not
     * something reconstructed by adding up a breakdown.
     *
     * Fallback: sum the campaign breakdown only (not keywords, not
     * search terms -- those are sub-breakdowns of that same campaign
     * spend, so adding them in as well would reintroduce exactly the
     * double/triple-counting bug this method exists to prevent).
     *
     * @param array<string,mixed>|null $accountLevelStats
     * @param array<int,array<string,mixed>> $campaigns
     * @return array<string,int|float>
     */
    private function resolveStatistics(?array $accountLevelStats, array $campaigns): array
    {
        $statistics = ['total_spend' => 0, 'total_conversions' => 0, 'total_clicks' => 0, 'total_impressions' => 0];

        if ($accountLevelStats !== null) {
            foreach (self::NUMERIC_FIELDS as $field) {
                if (isset($accountLevelStats[$field]) && is_numeric($accountLevelStats[$field])) {
                    $statistics['total_' . $field] = $accountLevelStats[$field];
                }
            }

            return $statistics;
        }

        foreach ($campaigns as $campaign) {
            foreach (self::NUMERIC_FIELDS as $field) {
                if (isset($campaign[$field]) && is_numeric($campaign[$field])) {
                    $statistics['total_' . $field] += $campaign[$field];
                }
            }
        }

        return $statistics;
    }

    private function normalizeField(string $fieldName, array $headerMap): string
    {
        $lower = strtolower(trim($fieldName));
        return $headerMap[$lower] ?? $lower;
    }

    private const DERIVED_NUMERIC_FIELDS = ['cpa', 'ctr', 'cpc'];

    private function cleanValue(string $normalizedField, string $rawValue)
    {
        if (!in_array($normalizedField, self::NUMERIC_FIELDS, true) && !in_array($normalizedField, self::DERIVED_NUMERIC_FIELDS, true)) {
            return $rawValue;
        }

        $stripped = preg_replace('/[^0-9.\-]+/', '', $rawValue) ?? '';
        return $stripped === '' || $stripped === '-' ? 0 : (float) $stripped;
    }

    /**
     * Classifies a fact-group row into one of the plugin's entity
     * categories.
     *
     * Tier 1 (exact): every column in `required_columns` is present --
     * the original, highest-confidence match.
     *
     * Tier 2 (fallback, by identifying column): AI-advisor-pasted tables
     * routinely omit one metric column that just wasn't part of that
     * particular answer (most often "spend" -- an advisor asked "list my
     * search terms" may not include a cost breakdown per term even
     * though it reported total account spend elsewhere). Requiring an
     * exact match on every column meant a single missing column silently
     * dropped an entire entity type (every campaign, every keyword) even
     * though the row clearly WAS a campaign/keyword/search-term row.
     * Tier 2 treats the FIRST entry in `required_columns` as that
     * category's identifying column (campaign_name, search_term,
     * keyword, device, location) -- its presence with a non-empty value
     * is enough to place the row; missing metric columns just render as
     * 0/-- downstream rather than making the whole row vanish.
     *
     * @param array<string,mixed> $row
     * @param array<string,array{required_columns?:array<int,string>}> $entityDefs
     */
    private function classify(array $row, array $entityDefs): ?string
    {
        $rowKeys = array_keys($row);

        $bestMatch = null;
        $bestScore = 0;
        foreach ($entityDefs as $category => $config) {
            $required = $config['required_columns'] ?? [];
            if ($required === []) {
                continue;
            }
            $present = array_intersect($required, $rowKeys);
            if (count($present) === count($required) && count($required) > $bestScore) {
                $bestMatch = $category;
                $bestScore = count($required);
            }
        }
        if ($bestMatch !== null) {
            return $bestMatch;
        }

        foreach ($entityDefs as $category => $config) {
            $required = $config['required_columns'] ?? [];
            $identifier = $required[0] ?? null;
            if ($identifier !== null && array_key_exists($identifier, $row) && trim((string) $row[$identifier]) !== '') {
                return $category;
            }
        }

        return null;
    }
}
