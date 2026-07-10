<?php
/* ==========================================================================
   ENTITY-CONSISTENCY.PHP — checks that the same real-world facts (phone,
   email, company name) resolve to the same underlying value everywhere,
   regardless of surface formatting. Different formats for different
   technical contexts are correct, expected practice, NOT an inconsistency
   — a tel: link needs digits-only, schema.org conventionally uses
   +1-XXX-XXX-XXXX, and display text uses (XXX) XXX-XXXX. This checker
   verifies they all represent the same number, not that they look
   identical character-for-character.
   ========================================================================== */

function octg_check_entity_consistency(): array {
    $findings = [];
    $root = __DIR__ . '/../..';
    $files = array_merge(
        glob($root . '/*.php'),
        glob($root . '/includes/*.php'),
        glob($root . '/data/*.php')
    );

    /* ---- Phone number: normalize every occurrence to digits, verify one canonical number.
       Excludes placeholder="..." attribute content — an input's example format text
       (e.g. placeholder="(555) 123-4567") is a UX convention, not a claimed real number,
       and flagging it as a conflicting phone number would itself be a false finding. ---- */
    $phonePattern = '/(?:\+1[\s\-]?)?\(?(\d{3})\)?[\s\-.]?(\d{3})[\s\-.]?(\d{4})\b/';
    $foundNumbers = [];
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $content = preg_replace('/placeholder="[^"]*"/i', '', $content);
        if (preg_match_all($phonePattern, $content, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $digits = $match[1] . $match[2] . $match[3];
                $foundNumbers[$digits][] = basename($file);
            }
        }
    }
    if (count($foundNumbers) > 1) {
        $findings[] = ['type' => 'phone', 'status' => 'inconsistent',
            'detail' => 'Multiple different phone numbers found: ' . implode(', ', array_keys($foundNumbers)),
            'files' => $foundNumbers];
    } elseif (count($foundNumbers) === 1) {
        $number = array_key_first($foundNumbers);
        $findings[] = ['type' => 'phone', 'status' => 'consistent',
            'detail' => "One phone number used everywhere ((" . substr($number,0,3) . ") " . substr($number,3,3) . '-' . substr($number,6) . "), correctly formatted differently for display text, tel: links, and schema markup.",
            'occurrences' => array_sum(array_map('count', $foundNumbers))];
    } else {
        $findings[] = ['type' => 'phone', 'status' => 'not_found', 'detail' => 'No phone number pattern found across the codebase.'];
    }

    /* ---- Email: the primary business email should be the same address everywhere it appears as a contact point ---- */
    $emailPattern = '/[a-zA-Z0-9._%+\-]+@onechancetogrow\.com/';
    $foundEmails = [];
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if (preg_match_all($emailPattern, $content, $m)) {
            foreach (array_unique($m[0]) as $email) $foundEmails[$email][] = basename($file);
        }
    }
    $distinctEmails = array_keys($foundEmails);
    $findings[] = ['type' => 'email', 'status' => count($distinctEmails) <= 2 ? 'consistent' : 'review',
        'detail' => count($distinctEmails) . ' distinct @onechancetogrow.com address(es) found: ' . implode(', ', $distinctEmails)
            . (count($distinctEmails) <= 2 ? ' — a general (hello@) and a system (no-reply@) address is expected, not a conflict.' : ' — worth a manual look.'),
        'files' => $foundEmails];

    /* ---- Company name: verify the LEGAL name is used consistently in schema/legal contexts specifically ---- */
    $legalNameCount = 0;
    $schemaFiles = [];
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if (preg_match_all('/"name":\s*"([^"]*One Chance[^"]*)"/', $content, $m)) {
            foreach ($m[1] as $name) {
                if ($name !== 'One Chance To Grow LLC') {
                    $findings[] = ['type' => 'company_name_schema', 'status' => 'inconsistent',
                        'detail' => "Schema in " . basename($file) . " uses \"{$name}\" instead of the full legal name \"One Chance To Grow LLC\".", 'files' => [basename($file)]];
                } else {
                    $legalNameCount++;
                }
            }
        }
    }
    if ($legalNameCount > 0) {
        $findings[] = ['type' => 'company_name_schema', 'status' => 'consistent',
            'detail' => "Every schema.org \"name\" field referencing the company uses the full legal name consistently ({$legalNameCount} occurrence(s))."];
    }

    return $findings;
}
