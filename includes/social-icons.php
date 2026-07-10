<?php
/* ==========================================================================
   SOCIAL-ICONS.PHP — shared helper for rendering a social/contact icon link.
   Uses simple generic glyphs (not reproductions of official platform logos)
   to keep this trademark-safe while still being instantly recognizable.
   ========================================================================== */

function octg_social_icon(string $platform, string $url): string {
    $icons = [
        'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7.5v.01M12 17v-4.5a2 2 0 0 1 4 0V17" stroke-linecap="round"/>',
        'x'        => '<path d="M5 5l14 14M19 5L5 19" stroke-linecap="round"/>',
        'email'    => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m4 6 8 6 8-6" stroke-linecap="round" stroke-linejoin="round"/>',
    ];
    $path = $icons[$platform] ?? $icons['email'];
    $label = ucfirst($platform === 'x' ? 'X (Twitter)' : $platform);

    return '<a href="' . htmlspecialchars($url) . '" class="social-icon" aria-label="' . htmlspecialchars($label) . '" target="_blank" rel="noopener noreferrer">'
         . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">' . $path . '</svg>'
         . '</a>';
}
