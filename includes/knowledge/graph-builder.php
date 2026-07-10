<?php
/* ==========================================================================
   GRAPH-BUILDER.PHP — builds the business knowledge graph LIVE from the
   real catalog files, every time it's requested. Nothing about the graph
   is stored/duplicated in a database — the catalogs already ARE the source
   of truth (same reasoning as the optimizer's page-inventory.php), so
   building it fresh each time means it can never drift out of sync.

   Every edge is tagged 'direct' (an explicit field in the source data) or
   'derived' (computed by following a chain of direct edges — e.g. Product
   → Case Study only exists by way of Product → Service → Case Study).
   Derived edges are never presented as if they were direct facts.

   Documented industry taxonomy note: services-catalog.php categorizes
   services into 6 groups (websites, visibility, reputation, advertising,
   automation, software); resources-catalog.php uses a different 4-group
   taxonomy (seo, marketing, automation, strategy) that was never unified
   with the services one. octg_topic_taxonomy_map() below is an explicit,
   documented bridge between them — an interpretive mapping, not a fact
   found in the data, and it's kept in exactly one place so it's easy to
   audit or change.
   ========================================================================== */

function octg_topic_taxonomy_map(): array {
    // resources-catalog.php category => services-catalog.php category
    return ['seo' => 'visibility', 'marketing' => 'reputation', 'automation' => 'automation', 'strategy' => 'software'];
}

function octg_build_knowledge_graph(): array {
    $services = require __DIR__ . '/../../data/services-catalog.php';
    $products = require __DIR__ . '/../../data/products-catalog.php';
    $categories = require __DIR__ . '/../../data/categories.php';
    $articles = require __DIR__ . '/../../data/resources-catalog.php';
    $cases = require __DIR__ . '/../../data/case-studies-catalog.php';
    $team = require __DIR__ . '/../../data/team-catalog.php';

    // Industries live inline in industries.php, not a data/ catalog file —
    // extracted here via the same regex approach used to keep everything
    // else in sync with real source rather than a hand-copied duplicate.
    $industriesSrc = file_get_contents(__DIR__ . '/../../industries.php');
    preg_match_all("/'name' => '([^']+)', 'service' => '([a-z0-9\-]+)'/", $industriesSrc, $m, PREG_SET_ORDER);
    $industries = array_map(fn($row) => ['name' => $row[1], 'linked_service' => $row[2]], $m);

    $nodes = [];
    $edges = []; // each: [from_type, from_id, to_type, to_id, relationship, 'direct'|'derived', via]

    $nodes['company'] = ['type' => 'company', 'id' => 'octg', 'label' => 'One Chance To Grow LLC'];

    foreach ($services as $s) {
        $nodes['service:' . $s['slug']] = ['type' => 'service', 'id' => $s['slug'], 'label' => $s['name'], 'category' => $s['category']];
        $edges[] = ['company', 'octg', 'service', $s['slug'], 'offers', 'direct', 'services-catalog.php'];

        foreach ($s['related'] as $relSlug) {
            $edges[] = ['service', $s['slug'], 'service', $relSlug, 'related_to', 'direct', 'services-catalog.php:related'];
        }
        if (!empty($s['related_product'])) {
            $edges[] = ['service', $s['slug'], 'product', $s['related_product'], 'paired_with', 'direct', 'services-catalog.php:related_product'];
        }
    }

    foreach ($products as $p) {
        $nodes['product:' . $p['slug']] = ['type' => 'product', 'id' => $p['slug'], 'label' => $p['name']];
        foreach ($p['pairs_with'] as $svcSlug) {
            $edges[] = ['product', $p['slug'], 'service', $svcSlug, 'pairs_with', 'direct', 'products-catalog.php:pairs_with'];
        }
    }

    foreach ($industries as $ind) {
        $indId = 'ind_' . preg_replace('/[^a-z0-9]+/', '-', strtolower($ind['name']));
        $nodes['industry:' . $indId] = ['type' => 'industry', 'id' => $indId, 'label' => $ind['name']];
        $edges[] = ['industry', $indId, 'service', $ind['linked_service'], 'features', 'direct', 'industries.php'];
    }

    foreach ($cases as $c) {
        $nodes['case_study:' . $c['slug']] = ['type' => 'case_study', 'id' => $c['slug'], 'label' => $c['title']];
        foreach ($c['services_used'] as $svcSlug) {
            $edges[] = ['case_study', $c['slug'], 'service', $svcSlug, 'demonstrates', 'direct', 'case-studies-catalog.php:services_used'];
        }
        // Case study -> Industry is a name-match against industries.php's
        // industry names, not a stored slug reference — real, but string-
        // matched rather than ID-linked, so it's tagged 'derived'.
        foreach ($industries as $ind) {
            if (strcasecmp($ind['name'], $c['industry']) === 0) {
                $indId = 'ind_' . preg_replace('/[^a-z0-9]+/', '-', strtolower($ind['name']));
                $edges[] = ['case_study', $c['slug'], 'industry', $indId, 'belongs_to', 'derived', 'name-matched against industries.php'];
            }
        }
    }

    foreach ($articles as $a) {
        $nodes['article:' . $a['slug']] = ['type' => 'article', 'id' => $a['slug'], 'label' => $a['title'], 'category' => $a['category']];
        foreach ($a['related_services'] as $svcSlug) {
            $edges[] = ['article', $a['slug'], 'service', $svcSlug, 'explains', 'direct', 'resources-catalog.php:related_services'];
        }
    }

    foreach ($team as $t) {
        $nodes['team:' . $t['slug']] = ['type' => 'team', 'id' => $t['slug'], 'label' => $t['name'] . ' (' . $t['title'] . ')'];
        $edges[] = ['company', 'octg', 'team', $t['slug'], 'employs', 'direct', 'team-catalog.php'];
    }

    // Derived: Product -> Case Study, only reachable via Service (Product
    // pairs_with Service, Service demonstrated-by Case Study). Never stored
    // as if it were a direct fact.
    $productToServices = [];
    foreach ($edges as $e) {
        if ($e[0] === 'product' && $e[4] === 'pairs_with') $productToServices[$e[1]][] = $e[3];
    }
    $serviceToCases = [];
    foreach ($edges as $e) {
        if ($e[0] === 'case_study' && $e[4] === 'demonstrates') $serviceToCases[$e[3]][] = $e[1];
    }
    foreach ($productToServices as $productSlug => $svcSlugs) {
        foreach ($svcSlugs as $svcSlug) {
            foreach (($serviceToCases[$svcSlug] ?? []) as $caseSlug) {
                $edges[] = ['product', $productSlug, 'case_study', $caseSlug, 'demonstrated_by', 'derived', "via service '{$svcSlug}'"];
            }
        }
    }

    return ['nodes' => array_values($nodes), 'edges' => $edges, 'raw' => compact('services', 'products', 'industries', 'articles', 'cases', 'team', 'categories')];
}
