<?php

declare(strict_types=1);

namespace App\Services\AIParser;

class NLParser
{
    /**
     * Parses a natural language string and extracts structured data with confidence scores.
     *
     * @param string $rawText
     * @param string $expectedSection (e.g., 'kpis', 'campaigns', 'recommendations')
     * @return array
     */
    public function parse(string $rawText, string $expectedSection): array
    {
        $rawText = trim($rawText);
        if (empty($rawText)) {
            return [];
        }

        // Repairs a markdown table that got pasted in as one squished line
        // instead of one row per line (common when copying a rendered
        // table out of a chat UI) -- a no-op for JSON or already-normal
        // text, see MarkdownTableSanitizer's docblock for why this exists.
        $rawText = MarkdownTableSanitizer::sanitize($rawText);

        // Every prompt in app/Prompts/google_ads/*.md explicitly instructs
        // the AI to "Return ONLY valid JSON" -- JSON is the format every
        // section actually asks for, so it's tried first via JSONParser
        // (already built for this, just never wired in here). The old
        // regex/markdown-table/bullet-list heuristics below only run as a
        // fallback for a response that didn't come back as valid JSON --
        // e.g. an advisor that ignored the format instruction -- so a
        // slightly messy paste still extracts *something* rather than
        // nothing.
        $jsonResult = $this->tryParseAsJson($rawText, $expectedSection);
        if ($jsonResult !== null) {
            return $jsonResult;
        }

        switch ($expectedSection) {
            case 'kpis':
                return $this->extractKPIs($rawText);
            case 'campaigns':
            case 'keywords':
            case 'search_terms':
                return $this->extractTable($rawText, $expectedSection);
            case 'recommendations':
            case 'opportunities':
                return $this->extractLists($rawText, $expectedSection);
            case 'executive_summary':
                return $this->extractExecutiveSummary($rawText);
            default:
                return [];
        }
    }

    /**
     * Attempts to parse $rawText as the JSON shape documented in
     * app/Prompts/google_ads/{section}.md, and re-wraps every leaf value
     * in the same {value, confidence, source_line} envelope the regex/
     * table/bullet-list extractors below produce -- so review_ai.php's
     * form rendering and AuditMerger's fact-writing don't need to know or
     * care whether a given response came back as JSON or natural
     * language; both paths return an identical shape.
     *
     * @return array|null null means "not valid JSON for this section,
     *         fall back to the natural-language heuristics"
     */
    private function tryParseAsJson(string $rawText, string $expectedSection): ?array
    {
        $jsonParser = new JSONParser();
        $decoded = $jsonParser->parse($rawText);
        if ($decoded === null || !is_array($decoded)) {
            return null;
        }

        // Every section prompt wraps its payload in a top-level key that
        // matches the section name (e.g. {"campaigns": [...]}) -- but
        // tolerate an AI that returned the inner array/object directly.
        $payload = $decoded[$expectedSection] ?? $decoded;
        if (!is_array($payload) || $payload === []) {
            return null;
        }

        $source = 'AI JSON response';

        switch ($expectedSection) {
            case 'kpis':
                $results = [];
                foreach ($payload as $key => $val) {
                    if ($val === null || $val === '' || is_array($val)) {
                        continue;
                    }
                    $results[$key] = [
                        'value' => is_numeric($val) ? (float) $val : $val,
                        'confidence' => 100,
                        'source_line' => $source,
                        'rule' => 'json_structured',
                    ];
                }
                return $results === [] ? null : $results;

            case 'campaigns':
            case 'keywords':
            case 'search_terms':
                $results = [];
                foreach ($payload as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $row = [];
                    foreach ($item as $key => $val) {
                        $scalar = is_array($val) ? json_encode($val) : (string) $val;
                        $row[$key] = [
                            'value' => $scalar,
                            'parsed_value' => is_numeric($val) ? (float) $val : $scalar,
                            'confidence' => 100,
                            'source_line' => $source,
                        ];
                    }
                    if ($row !== []) {
                        $results[] = $row;
                    }
                }
                return $results === [] ? null : $results;

            case 'recommendations':
            case 'opportunities':
                $results = [];
                foreach ($payload as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $row = [];
                    foreach ($item as $key => $val) {
                        $scalar = is_array($val) ? json_encode($val) : (string) $val;
                        $row[$key] = [
                            'value' => $scalar,
                            'parsed_value' => $scalar,
                            'confidence' => 100,
                            'source_line' => $source,
                        ];
                    }
                    if ($row !== []) {
                        $results[] = $row;
                    }
                }
                return $results === [] ? null : $results;

            case 'executive_summary':
                $sections = [];
                foreach ($payload as $key => $val) {
                    if (is_array($val)) {
                        $sections[$key] = [];
                        foreach ($val as $listItem) {
                            if ($listItem === null || $listItem === '') {
                                continue;
                            }
                            $sections[$key][] = [
                                'value' => is_array($listItem) ? json_encode($listItem) : (string) $listItem,
                                'confidence' => 100,
                                'source_line' => $source,
                            ];
                        }
                    } elseif ($val !== null && $val !== '') {
                        $sections[$key] = [
                            'value' => (string) $val,
                            'confidence' => 100,
                            'source_line' => $source,
                        ];
                    }
                }
                return $sections === [] ? null : $sections;

            default:
                return null;
        }
    }

    private function extractKPIs(string $text): array
    {
        $kpis = [
            'spend' => ['patterns' => ['/spend.*?\$?([\d,]+\.?\d*)/i', '/cost.*?\$?([\d,]+\.?\d*)/i', '/amount spent.*?\$?([\d,]+\.?\d*)/i']],
            'clicks' => ['patterns' => ['/clicks.*?([\d,]+)/i']],
            'impressions' => ['patterns' => ['/impressions.*?([\d,]+)/i']],
            'conversions' => ['patterns' => ['/conversions.*?([\d,]+(?:\.\d+)?)/i']],
            'ctr' => ['patterns' => ['/ctr.*?([\d\.]+)\s*%/i', '/click[\s-]*through[\s-]*rate.*?([\d\.]+)\s*%/i']],
            'cpc' => ['patterns' => ['/cpc.*?\$?([\d\.]+)/i', '/cost[\s-]*per[\s-]*click.*?\$?([\d\.]+)/i']],
            'cpa' => ['patterns' => ['/cpa.*?\$?([\d\.]+)/i', '/cost[\s-]*per[\s-]*acquisition.*?\$?([\d\.]+)/i']],
            'conversion_rate' => ['patterns' => ['/conversion rate.*?([\d\.]+)\s*%/i']],
            'roas' => ['patterns' => ['/roas.*?([\d\.]+)\s*%/i', '/return on ad spend.*?([\d\.]+)\s*%/i']]
        ];

        $results = [];
        $lines = explode("\n", $text);

        foreach ($kpis as $key => $config) {
            $bestMatch = null;
            $bestConfidence = 0;
            $sourceLine = '';

            foreach ($lines as $line) {
                foreach ($config['patterns'] as $pattern) {
                    if (preg_match($pattern, $line, $matches)) {
                        $val = str_replace(',', '', $matches[1]);
                        
                        // Heuristic confidence
                        $confidence = 80;
                        if (strpos(strtolower($line), 'total ' . $key) !== false || strpos(strtolower($line), $key . ':') !== false) {
                            $confidence = 100;
                        }

                        if ($confidence > $bestConfidence) {
                            $bestConfidence = $confidence;
                            $bestMatch = $val;
                            $sourceLine = trim($line);
                        }
                    }
                }
            }

            if ($bestMatch !== null) {
                $results[$key] = [
                    'value' => is_numeric($bestMatch) ? (float)$bestMatch : $bestMatch,
                    'confidence' => $bestConfidence,
                    'source_line' => $sourceLine,
                    'rule' => 'regex_pattern_match'
                ];
            }
        }

        return $results;
    }

    private function extractTable(string $text, string $section): array
    {
        $lines = explode("\n", $text);
        $results = [];
        $inTable = false;
        $headers = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $isMarkdown = preg_match('/^\|.*\|$/', $line);
            $isTsv = strpos($line, "\t") !== false;

            $cols = [];
            if ($isMarkdown) {
                // Alignment/separator row (e.g. "| --- | :--- | ---: |").
                // The original pattern only recognized cells that START
                // with a hyphen, so a colon-first alignment marker like
                // ":---" (left-align -- what most AI advisors actually
                // produce, including Ask Advisor) fell through and got
                // ingested as a bogus data row of dashes instead of being
                // skipped. Any cell made up purely of hyphens/colons now
                // counts as a separator, regardless of which side the
                // colon is on.
                if (preg_match('/^\|(?:\s*[-:]+\s*\|)+$/', $line)) continue;
                $cols = array_map('trim', explode('|', trim($line, '|')));
            } elseif ($isTsv) {
                $cols = array_map('trim', explode("\t", $line));
            } else {
                continue;
            }
                
                if (!$inTable) {
                    $headers = array_map('strtolower', $cols);
                    $inTable = true;
                } else {
                    $row = [];
                    foreach ($cols as $i => $col) {
                        $header = $headers[$i] ?? "col_$i";
                        $header = str_replace(' ', '_', $header);
                        
                        // Clean currency and percentages
                        $val = preg_replace('/[^\d\.\-a-zA-Z\s]/', '', $col);
                        if (is_numeric(str_replace(',', '', $val))) {
                            $val = (float)str_replace(',', '', $val);
                        }

                        // Entity key mapping based on section
                        if ($section === 'campaigns' && strpos($header, 'campaign') !== false) $header = 'campaign_name';
                        if ($section === 'keywords' && strpos($header, 'keyword') !== false) $header = 'keyword';
                        if ($section === 'search_terms' && strpos($header, 'search') !== false) $header = 'search_term';

                        $row[$header] = [
                            'value' => trim((string)$col), // Keep original for display
                            'parsed_value' => $val,
                            'confidence' => 90,
                            'source_line' => $line
                        ];
                    }
                    $results[] = $row;
                }
            // removed extra brace
        }
        return $results;
    }

    private function extractLists(string $text, string $section): array
    {
        $lines = explode("\n", $text);
        $results = [];
        $currentItem = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                if ($currentItem !== null) {
                    $results[] = $currentItem;
                    $currentItem = null;
                }
                continue;
            }

            // Detect bullet point or numbered list
            if (preg_match('/^(\d+\.|\*|-|•)\s+(.*)$/', $line, $matches)) {
                if ($currentItem !== null) {
                    $results[] = $currentItem;
                }
                $content = $matches[2];

                // Pull an inline "(Priority: High)" tag out of the line if
                // present -- the recommendations/opportunities prompts ask
                // for a compact "What — Why (Priority: X)" single-line
                // format as their fallback, so an advisor that can't do
                // JSON is still expected to put priority on the same line
                // rather than a separate one.
                $priorityValue = 'Medium';
                $priorityConfidence = 50;
                $prioritySource = 'Default';
                if (preg_match('/\(?\s*priority\s*:\s*(high|medium|low)\s*\)?/i', $content, $pMatch)) {
                    $priorityValue = ucfirst(strtolower($pMatch[1]));
                    $priorityConfidence = 95;
                    $prioritySource = $line;
                    $content = trim((string) preg_replace('/\(?\s*priority\s*:\s*(high|medium|low)\s*\)?/i', '', $content));
                }

                // Pull an inline "what — why" split out of the same line.
                // Only an em/en dash (or a hyphen with a space on BOTH
                // sides) counts as the separator -- a bare hyphen inside a
                // word like "high-performing" must never be mistaken for
                // one, so the pattern requires whitespace on both sides.
                $whatText = $content;
                $whyText = '';
                if (preg_match('/^(.+?)\s+[—–]\s+(.+)$/u', $content, $splitMatch)
                    || preg_match('/^(.+?)\s+-\s+(.+)$/u', $content, $splitMatch)
                ) {
                    $whatText = trim($splitMatch[1]);
                    $whyText = trim($splitMatch[2]);
                }

                $currentItem = [
                    'what_to_change' => ['value' => $whatText, 'confidence' => 85, 'source_line' => $line],
                    'why_it_matters' => [
                        'value' => $whyText,
                        'confidence' => $whyText !== '' ? 80 : 50,
                        'source_line' => $whyText !== '' ? $line : '',
                    ],
                    'priority' => ['value' => $priorityValue, 'confidence' => $priorityConfidence, 'source_line' => $prioritySource],
                ];

                // Heuristic fallback for priority when no explicit tag was found.
                if ($priorityConfidence < 90 && (stripos($content, 'critical') !== false || stripos($content, 'high priority') !== false)) {
                    $currentItem['priority'] = ['value' => 'High', 'confidence' => 90, 'source_line' => $line];
                }
            } elseif ($currentItem !== null) {
                // Continuation of previous item (maybe 'why it matters')
                if (empty($currentItem['why_it_matters']['value'])) {
                    $currentItem['why_it_matters'] = ['value' => $line, 'confidence' => 70, 'source_line' => $line];
                } else {
                    $currentItem['why_it_matters']['value'] .= ' ' . $line;
                    $currentItem['why_it_matters']['source_line'] .= ' ' . $line;
                }
            } else {
                // No bullet point and no current item, treat line as new item
                $content = $line;
                $currentItem = [
                    'what_to_change' => ['value' => $content, 'confidence' => 85, 'source_line' => $line],
                    'why_it_matters' => ['value' => '', 'confidence' => 50, 'source_line' => ''],
                    'priority' => ['value' => 'Medium', 'confidence' => 50, 'source_line' => 'Default']
                ];
                if (stripos($content, 'critical') !== false || stripos($content, 'high priority') !== false) {
                    $currentItem['priority'] = ['value' => 'High', 'confidence' => 90, 'source_line' => $line];
                }
            }
        }

        if ($currentItem !== null) {
            $results[] = $currentItem;
        }

        return $results;
    }

    private function extractExecutiveSummary(string $text): array
    {
        $sections = [
            'executive_summary' => '',
            'biggest_wins' => [],
            'biggest_risks' => [],
            'immediate_actions' => [],
            'long_term_strategy' => ''
        ];

        $currentSection = 'executive_summary';
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $lower = strtolower($line);
            
            // Detect headers
            if (strpos($lower, 'biggest win') !== false || strpos($lower, 'key strength') !== false) {
                $currentSection = 'biggest_wins';
                continue;
            } elseif (strpos($lower, 'biggest risk') !== false || strpos($lower, 'threats') !== false) {
                $currentSection = 'biggest_risks';
                continue;
            } elseif (strpos($lower, 'immediate action') !== false || strpos($lower, 'next step') !== false) {
                $currentSection = 'immediate_actions';
                continue;
            } elseif (strpos($lower, 'long term') !== false || strpos($lower, 'strategy') !== false) {
                $currentSection = 'long_term_strategy';
                continue;
            }

            // Extract content
            if (is_array($sections[$currentSection])) {
                if (preg_match('/^(\d+\.|\*|-|•)\s+(.*)$/', $line, $matches)) {
                    $sections[$currentSection][] = [
                        'value' => $matches[2],
                        'confidence' => 90,
                        'source_line' => $line
                    ];
                }
            } else {
                if (empty($sections[$currentSection])) {
                    $sections[$currentSection] = [
                        'value' => $line,
                        'confidence' => 85,
                        'source_line' => $line
                    ];
                } else {
                    $sections[$currentSection]['value'] .= "\n" . $line;
                    $sections[$currentSection]['source_line'] .= "\n" . $line;
                }
            }
        }

        return $sections;
    }
}
