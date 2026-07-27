<?php

declare(strict_types=1);

namespace App\Services\AIParser;

class JSONParser
{
    /**
     * Extracts and decodes the first JSON block found in a markdown string.
     * If no markdown code block is found, attempts to parse the entire string.
     *
     * @param string $rawText
     * @return array|null The parsed JSON as an associative array, or null on failure.
     */
    public function parse(string $rawText): ?array
    {
        $rawText = trim($rawText);
        if (empty($rawText)) {
            return null;
        }

        // Try to find a JSON block ```json ... ``` or just ``` ... ```
        if (preg_match('/```(?:json)?\s*(\{.*\}|\[.*\])\s*```/is', $rawText, $matches)) {
            $jsonStr = $matches[1];
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try to parse the entire text (if AI didn't use markdown fences)
        $decoded = json_decode($rawText, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Try to aggressively extract { ... } or [ ... ]
        $firstBrace = strpos($rawText, '{');
        $lastBrace = strrpos($rawText, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $jsonStr = substr($rawText, $firstBrace, $lastBrace - $firstBrace + 1);
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        $firstBracket = strpos($rawText, '[');
        $lastBracket = strrpos($rawText, ']');
        if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
            $jsonStr = substr($rawText, $firstBracket, $lastBracket - $firstBracket + 1);
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }
}
