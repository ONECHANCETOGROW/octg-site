<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Parsing;

/**
 * Minimal, dependency-free PDF text extractor — no Composer package, no
 * shelling out to a system binary (shared hosting frequently disables
 * shell_exec). Uses only PHP's built-in zlib extension.
 *
 * How it works: a PDF's page content is stored in one or more compressed
 * "streams" (almost always FlateDecode, i.e. zlib-wrapped deflate — exactly
 * what PHP's gzuncompress() decodes). Once decompressed, a content stream is
 * a sequence of drawing/text operators; this extractor pulls text out of the
 * `Tj` (show one string) and `TJ` (show an array of strings/kerning
 * adjustments) operators, which is where visible text in a PDF actually
 * lives.
 *
 * Known, documented scope limits (see MARKETING_INTEL_ARCHITECTURE.md and
 * the Developer Handoff's "Known Limitations" section): handles
 * non-encrypted PDFs using FlateDecode content streams — true of the large
 * majority of programmatically-generated reports (which is exactly what an
 * ad platform's "export as PDF" produces). Does not OCR scanned/image-only
 * PDFs, does not decrypt password-protected files, and does not attempt to
 * preserve exact visual layout/column structure — it recovers a text stream
 * plus line breaks, which the same table/key-value detection used by
 * AiResponseParser is then reused against, since a PDF export's text often
 * still reads as a table once extracted.
 */
final class PdfParser implements FileParserInterface
{
    public function code(): string
    {
        return 'pdf_parser';
    }

    public function parseFile(string $filePath, array $expectedColumns): ParsedPayload
    {
        if (!is_file($filePath)) {
            return ParsedPayload::empty('pdf_file_not_found');
        }

        $bytes = file_get_contents($filePath);
        if ($bytes === false || $bytes === '') {
            return ParsedPayload::empty('pdf_unreadable');
        }

        $text = $this->extractText($bytes);
        if (trim($text) === '') {
            return ParsedPayload::empty('pdf_no_extractable_text');
        }

        // Once we have plain text, reuse the same table/key-value detection
        // AiResponseParser already implements rather than duplicating it —
        // a PDF export's extracted text is structurally similar to a pasted
        // AI response (rows of labelled figures).
        $delegate = new AiResponseParser();
        $payload = $delegate->parseText($text, $expectedColumns);

        if ($payload->isEmpty()) {
            return ParsedPayload::empty('pdf_text_extracted_but_unstructured');
        }

        return $payload;
    }

    private function extractText(string $pdfBytes): string
    {
        $streams = $this->extractFlateDecodeStreams($pdfBytes);
        $textParts = [];

        foreach ($streams as $stream) {
            $decoded = @gzuncompress($stream);
            if ($decoded === false) {
                // Some producers omit the zlib header/checksum; try raw
                // inflate as a fallback before giving up on this stream.
                $decoded = @gzinflate($stream);
            }

            if ($decoded === false || $decoded === '') {
                continue;
            }

            $textParts[] = $this->extractShowTextOperators($decoded);
        }

        return implode("\n", array_filter($textParts, static fn (string $t): bool => trim($t) !== ''));
    }

    /**
     * @return array<int,string> raw compressed bytes of each stream found
     */
    private function extractFlateDecodeStreams(string $pdfBytes): array
    {
        $streams = [];
        $offset = 0;

        while (($streamPos = strpos($pdfBytes, 'stream', $offset)) !== false) {
            // Look at the ~200 bytes before "stream" for the object's
            // dictionary to confirm it declares /FlateDecode — skips
            // image streams (DCTDecode/JPXDecode) we can't and don't need
            // to read for text extraction.
            $dictStart = max(0, $streamPos - 400);
            $dict = substr($pdfBytes, $dictStart, $streamPos - $dictStart);

            $endPos = strpos($pdfBytes, 'endstream', $streamPos);
            if ($endPos === false) {
                break;
            }

            if (str_contains($dict, '/FlateDecode')) {
                // "stream" is followed by CRLF or LF before the actual data.
                $dataStart = $streamPos + strlen('stream');
                if (substr($pdfBytes, $dataStart, 2) === "\r\n") {
                    $dataStart += 2;
                } elseif (substr($pdfBytes, $dataStart, 1) === "\n") {
                    $dataStart += 1;
                }

                $rawStream = substr($pdfBytes, $dataStart, $endPos - $dataStart);
                $streams[] = rtrim($rawStream, "\r\n");
            }

            $offset = $endPos + strlen('endstream');
        }

        return $streams;
    }

    private function extractShowTextOperators(string $contentStream): string
    {
        $output = [];

        // TJ: an array of strings/number kerning adjustments, e.g.
        // [(Campaign)-4(Performance)] TJ
        if (preg_match_all('/\[((?:[^\[\]\\\\]|\\\\.)*)\]\s*TJ/s', $contentStream, $tjMatches) > 0) {
            foreach ($tjMatches[1] as $arrayContents) {
                if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/s', $arrayContents, $stringMatches) > 0) {
                    $output[] = implode('', array_map(
                        fn (string $s): string => $this->decodePdfString($s),
                        $stringMatches[1]
                    ));
                }
            }
        }

        // Tj: a single string, e.g. (Total Spend: $12,400) Tj
        if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/s', $contentStream, $tjSingle) > 0) {
            foreach ($tjSingle[1] as $string) {
                $output[] = $this->decodePdfString($string);
            }
        }

        // Td / TD / T* mark the start of a new line of text in PDF's text
        // positioning model — insert a newline so rows don't run together.
        $withBreaks = preg_replace('/(Td|TD|T\*)/', "$1\n", $contentStream) ?? $contentStream;
        $lineCount = substr_count($withBreaks, "\n");

        // If we found show-text operators, join them; otherwise there was
        // nothing to extract from this stream (e.g. a pure vector-graphics
        // stream with no text).
        if ($output === []) {
            return '';
        }

        // Heuristic: break into roughly one line per detected positioning
        // operator if the operator count plausibly matches the text-chunk
        // count, otherwise fall back to one line per extracted chunk.
        if ($lineCount > 0 && $lineCount >= count($output) - 2) {
            return implode("\n", $output);
        }

        return implode(' ', $output);
    }

    private function decodePdfString(string $raw): string
    {
        $map = [
            '\\(' => '(',
            '\\)' => ')',
            '\\\\' => '\\',
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
        ];

        $decoded = strtr($raw, $map);

        // Octal escapes, e.g. \050 for "(".
        $decoded = preg_replace_callback('/\\\\([0-7]{1,3})/', static function (array $m): string {
            return chr((int) octdec($m[1]));
        }, $decoded);

        return $decoded ?? $raw;
    }
}
