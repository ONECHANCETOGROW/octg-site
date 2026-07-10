<?php
/* ==========================================================================
   TOPICAL-AUTHORITY.PHP — measures real content coverage per topic cluster,
   using the services-catalog.php category taxonomy (6 groups) as the
   canonical topic list, and the documented taxonomy bridge from
   graph-builder.php to map resource articles into the same 6 groups.
   ========================================================================== */

function octg_measure_topical_authority(array $graph): array {
    $raw = $graph['raw'];
    $categories = $raw['categories']; // category_key => label
    $taxonomyMap = octg_topic_taxonomy_map();

    $coverage = [];
    foreach ($categories as $catKey => $catLabel) {
        $coverage[$catKey] = [
            'label' => $catLabel, 'services' => 0, 'articles' => 0, 'case_studies' => 0,
            'industries' => 0, 'faqs' => 0,
        ];
    }

    foreach ($raw['services'] as $s) {
        if (isset($coverage[$s['category']])) {
            $coverage[$s['category']]['services']++;
            $coverage[$s['category']]['faqs'] += count($s['faq'] ?? []); // every service page has its own FAQ
        }
    }

    foreach ($raw['articles'] as $a) {
        $mappedCat = $taxonomyMap[$a['category']] ?? null;
        if ($mappedCat && isset($coverage[$mappedCat])) $coverage[$mappedCat]['articles']++;
    }

    // Case studies -> category, via the services they demonstrate
    foreach ($graph['edges'] as $e) {
        if ($e[0] === 'case_study' && $e[4] === 'demonstrates') {
            foreach ($raw['services'] as $s) {
                if ($s['slug'] === $e[3] && isset($coverage[$s['category']])) {
                    $coverage[$s['category']]['case_studies']++;
                }
            }
        }
    }

    // Industries -> category, via their linked service
    foreach ($raw['industries'] as $ind) {
        foreach ($raw['services'] as $s) {
            if ($s['slug'] === $ind['linked_service'] && isset($coverage[$s['category']])) {
                $coverage[$s['category']]['industries']++;
            }
        }
    }

    $recommendations = [];
    foreach ($coverage as $catKey => $data) {
        $supportTypes = ['articles', 'case_studies', 'industries'];
        $missing = array_filter($supportTypes, fn($t) => $data[$t] === 0);
        if ($missing) {
            $recommendations[] = [
                'category' => $catKey, 'label' => $data['label'],
                'missing' => array_values($missing),
                'detail' => "\"{$data['label']}\" has {$data['services']} service page(s) but zero " . implode(' and zero ', $missing) . " connected to it.",
            ];
        }
    }

    return ['coverage' => $coverage, 'recommendations' => $recommendations];
}
