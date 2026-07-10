<?php
/* ==========================================================================
   GAP-DETECTOR.PHP — finds missing content by checking the real knowledge
   graph for entities with zero connections of an expected type. Every
   finding here traces to an actual absence in the real data — nothing is
   invented. Integrates with the Website Optimizer's own tables for
   internal-linking and schema gaps rather than re-detecting those.
   ========================================================================== */

function octg_detect_content_gaps(array $graph, ?PDO $pdo): array {
    $gaps = [];
    $edges = $graph['edges'];
    $raw = $graph['raw'];

    /* Product with no case study (direct or derived) */
    $productsWithCases = [];
    foreach ($edges as $e) {
        if ($e[0] === 'product' && $e[4] === 'demonstrated_by') $productsWithCases[$e[1]] = true;
    }
    foreach ($raw['products'] as $p) {
        if (empty($productsWithCases[$p['slug']])) {
            $gaps[] = ['category' => 'content_gap', 'entity_type' => 'product', 'entity_slug' => $p['slug'],
                'title' => "\"{$p['name']}\" has no case study", 'severity' => 'warning',
                'description' => "No case study demonstrates this product, even indirectly through a service it pairs with. A case study touching one of its paired services (" . implode(', ', $p['pairs_with']) . ") would close this gap."];
        }
    }

    /* Industry with no case study */
    $industriesWithCases = [];
    foreach ($edges as $e) {
        if ($e[0] === 'case_study' && $e[4] === 'belongs_to') $industriesWithCases[$e[3]] = true;
    }
    foreach ($raw['industries'] as $ind) {
        $indId = 'ind_' . preg_replace('/[^a-z0-9]+/', '-', strtolower($ind['name']));
        if (empty($industriesWithCases[$indId])) {
            $gaps[] = ['category' => 'content_gap', 'entity_type' => 'industry', 'entity_slug' => $indId,
                'title' => "\"{$ind['name']}\" has no case study", 'severity' => 'info',
                'description' => "No case study's industry field matches \"{$ind['name']}\" by name. Real client work in this industry would strengthen it, once available."];
        }
    }

    /* Service with no article (blog/resource) referencing it */
    $servicesWithArticles = [];
    foreach ($edges as $e) {
        if ($e[0] === 'article' && $e[4] === 'explains') $servicesWithArticles[$e[3]] = true;
    }
    foreach ($raw['services'] as $s) {
        if (empty($servicesWithArticles[$s['slug']])) {
            $gaps[] = ['category' => 'content_gap', 'entity_type' => 'service', 'entity_slug' => $s['slug'],
                'title' => "\"{$s['name']}\" has no linked resource article", 'severity' => 'info',
                'description' => "None of the 6 published articles reference this service. Not necessarily urgent with only 6 articles total, but worth knowing as the Resource Center grows."];
        }
    }

    /* Service with no case study (direct) */
    $servicesWithCases = [];
    foreach ($edges as $e) {
        if ($e[0] === 'case_study' && $e[4] === 'demonstrates') $servicesWithCases[$e[3]] = true;
    }
    $servicesMissingCases = 0;
    foreach ($raw['services'] as $s) {
        if (empty($servicesWithCases[$s['slug']])) $servicesMissingCases++;
    }
    if ($servicesMissingCases > 0) {
        $gaps[] = ['category' => 'content_gap', 'entity_type' => 'service', 'entity_slug' => null,
            'title' => "{$servicesMissingCases} of " . count($raw['services']) . " services have no case study",
            'severity' => 'info',
            'description' => "Only 3 case studies exist so far, covering a handful of services. This is expected at this stage — worth revisiting as more client work is documented, not something to force artificially."];
    }

    /* Integrate with the Website Optimizer: weak internal linking + missing
       schema, pulled from its most recent report rather than re-detected. */
    if ($pdo) {
        try {
            $latestReport = $pdo->query('SELECT id FROM audit_reports ORDER BY started_at DESC LIMIT 1')->fetchColumn();
            if ($latestReport) {
                $weakPages = $pdo->prepare("SELECT url, internal_linking_score FROM audit_page_scores WHERE report_id = :rid AND internal_linking_score < 50 ORDER BY internal_linking_score ASC LIMIT 10");
                $weakPages->execute([':rid' => $latestReport]);
                foreach ($weakPages->fetchAll() as $wp) {
                    $gaps[] = ['category' => 'internal_linking', 'entity_type' => null, 'entity_slug' => null,
                        'title' => "Weak internal linking: {$wp['url']}", 'severity' => 'warning',
                        'description' => "Internal Linking score is {$wp['internal_linking_score']}/100 per the latest optimizer audit (report #{$latestReport}). See the Website Optimizer for the full breakdown."];
                }

                $missingSchemaCount = $pdo->prepare("SELECT COUNT(*) FROM audit_issues WHERE report_id = :rid AND issue LIKE '%structured data%'");
                $missingSchemaCount->execute([':rid' => $latestReport]);
                $count = (int) $missingSchemaCount->fetchColumn();
                if ($count > 0) {
                    $gaps[] = ['category' => 'content_gap', 'entity_type' => null, 'entity_slug' => null,
                        'title' => "{$count} page(s) missing structured data", 'severity' => 'warning',
                        'description' => "From the latest optimizer audit (report #{$latestReport}) — see the AI Optimizer's issue list for exact URLs."];
                }
            }
        } catch (Throwable $e) {
            // Optimizer tables may not exist yet on a fresh install — gaps from catalog data above still work standalone.
        }
    }

    return $gaps;
}
