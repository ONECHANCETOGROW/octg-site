<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Knowledge;

use App\Modules\MarketingIntel\AuditRepository;
use App\Modules\MarketingIntel\KnowledgeFactRepository;
use App\Modules\MarketingIntel\RequirementRepository;

/**
 * The seam RNS spec §12 calls "Knowledge Builder Integration": every fact
 * this class returns is already fully merged and source-agnostic — nothing
 * that reads from here can tell whether a figure came from a pasted AI
 * response, an uploaded CSV, or (eventually) a live API call, and none of
 * it exposes which CollectionAttempt produced it.
 *
 * Important, honest scope note (this matters — see the Developer Handoff
 * Package, "Known Limitations"): this codebase has no pre-existing Rule
 * Engine, Scoring Engine, Opportunity Engine, Recommendation Engine,
 * Executive Summary generator, or Contract Builder for the marketing-
 * intelligence domain — those exist for the *SEO* product
 * (App\Modules\SeoEngine, App\Modules\Scoring, App\Modules\Intelligence) but
 * operate on crawled website data, not on Google Ads/GA4-style facts, and
 * were never built to consume this schema. This class is the clean,
 * genuinely-working hand-off point for whichever of those engines gets
 * built next for this domain — it does not fabricate a call into something
 * that doesn't exist. See §21 of the RNS spec for the recommended build
 * order once that work is scoped.
 */
final class KnowledgeBuilderAdapter
{
    public function __construct(
        private readonly KnowledgeFactRepository $facts,
        private readonly RequirementRepository $requirements,
        private readonly AuditRepository $audits
    ) {
    }

    /**
     * Every KnowledgeFact for an audit, grouped into a nested
     * entity_type -> entity_key -> field_name -> value/confidence shape —
     * the Marketing Intelligence Schema (MIS) representation described in
     * RNS spec §10, with provenance fields deliberately stripped out.
     *
     * @return array<string,array<string,array<string,array{value:string,unit:?string,confidence:int}>>>
     */
    public function factsForAudit(int $auditId): array
    {
        $rows = $this->facts->allForAudit($auditId);
        $grouped = [];

        foreach ($rows as $row) {
            $entityType = (string) $row['entity_type'];
            $entityKey = (string) $row['entity_key'];
            $fieldName = (string) $row['field_name'];

            $grouped[$entityType][$entityKey][$fieldName] = [
                'value' => (string) $row['value'],
                'unit' => $row['unit'] !== null ? (string) $row['unit'] : null,
                'confidence' => (int) $row['confidence'],
            ];
        }

        return $grouped;
    }

    /**
     * The "coverage map" from RNS spec §13 — which requirements were
     * satisfied, so a future Opportunity/Recommendation/Executive-Summary
     * engine can honestly caveat or suppress conclusions that depend on
     * data that was never collected, instead of silently omitting them or
     * inferring from unrelated data.
     *
     * @return array<int,array{requirement_id:int,code:string,title:string,category:string,is_required:bool,is_satisfied:bool}>
     */
    public function coverageMap(int $auditId): array
    {
        $audit = $this->audits->find($auditId);
        if ($audit === null) {
            return [];
        }

        $channels = $this->audits->channelsForAudit($auditId);
        $channelIds = array_map(static fn (array $c): int => (int) $c['id'], $channels);
        $requirements = $this->requirements->forChannels($channelIds);

        $satisfiedEntityFields = [];
        foreach ($this->facts->allForAudit($auditId) as $fact) {
            $satisfiedEntityFields[(int) $fact['requirement_id']] = true;
        }

        $coverage = [];
        foreach ($requirements as $requirement) {
            $requirementId = (int) $requirement['id'];
            $coverage[] = [
                'requirement_id' => $requirementId,
                'code' => (string) $requirement['code'],
                'title' => (string) $requirement['title'],
                'category' => (string) $requirement['category'],
                'is_required' => (bool) $requirement['is_required'],
                'is_satisfied' => isset($satisfiedEntityFields[$requirementId]),
            ];
        }

        return $coverage;
    }
}
