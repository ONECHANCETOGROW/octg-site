<?php
/* ==========================================================================
   SOCIAL-ICONS.PHP — shared helper for rendering a social/contact icon link.
   Uses simple generic glyphs (not reproductions of official platform logos)
   to keep this trademark-safe while still being instantly recognizable.
   ========================================================================== */

function octg_social_icon(string $platform, string $url): string {
    $platform = strtolower(trim($platform));
    if ($platform === 'twitter') { $platform = 'x'; }
    if ($url === '') { return ''; }

    $icons = [
        'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7.5v.01M12 17v-4.5a2 2 0 0 1 4 0V17" stroke-linecap="round"/>',
        'facebook'  => '<path d="M15 8h-2a2 2 0 0 0-2 2v2H9v3h2v6h3v-6h2.2l.8-3H14v-1.5a.5.5 0 0 1 .5-.5H16V8Z" stroke-linejoin="round"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/>',
        'x'         => '<path d="M5 5l14 14M19 5L5 19" stroke-linecap="round"/>',
        'website'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/>',
        'email'     => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m4 6 8 6 8-6" stroke-linecap="round" stroke-linejoin="round"/>',
    ];
    if (!isset($icons[$platform])) { return ''; }
    $path = $icons[$platform];
    $label = $platform === 'x' ? 'X (Twitter)' : ucfirst($platform);
    $isEmail = $platform === 'email';

    return '<a href="' . htmlspecialchars($url) . '" class="social-icon" aria-label="' . htmlspecialchars($label) . '"'
         . ($isEmail ? '' : ' target="_blank" rel="noopener noreferrer"')
         . '><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">' . $path . '</svg>'
         . '</a>';
}
