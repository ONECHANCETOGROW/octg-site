<?php
/* ==========================================================================
   CMS-MEDIA.PHP — one function, used by every page that displays an image.
   Renders a real <img> if the CMS has one, otherwise the branded
   placeholder — so nothing on the page needs to change once real photos
   are uploaded through data/cms-images.php (or a future admin panel writing
   to that same file).

   Usage: octg_media('hero_image', 'Hero Visual');
   Wrap the call in a .media-frame div for correct sizing (see components.css).
   ========================================================================== */

function octg_media(string $key, string $label, string $alt = '', bool $round = false, bool $eager = false): void {
    $url = null;
    $dbAlt = $alt;

    // Fetch from database
    $mediaData = octg_get_media_data($key);
    if ($mediaData) {
        $url = $mediaData['file_path'];
        if (empty($dbAlt) && !empty($mediaData['alt_text'])) {
            $dbAlt = $mediaData['alt_text'];
        }
        if (empty($dbAlt) && !empty($mediaData['title'])) {
            $dbAlt = $mediaData['title'];
        }
    } else {
        // Fallback to static config if DB lookup fails or is not setup yet
        static $images = null;
        if ($images === null) {
            $path = __DIR__ . '/../data/cms-images.php';
            $images = file_exists($path) ? require $path : [];
        }
        $url = $images[$key] ?? null;
    }

    $roundClass = $round ? ' is-round' : '';

    if ($url) {
        /* Above-the-fold hero images should load eagerly (and be marked
           high fetchpriority) since a lazy-loaded LCP candidate actually
           hurts Core Web Vitals rather than helping them. Everything else
           defaults to lazy. */
        $loadingAttr = $eager ? 'fetchpriority="high"' : 'loading="lazy"';
        echo '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($dbAlt ?: $label)
           . '" class="media-real' . $roundClass . '" ' . $loadingAttr . '>';
        return;
    }

    echo '<div class="media-placeholder' . $roundClass . '" data-cms-key="' . htmlspecialchars($key) . '">'
       . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 16l-5-4-4 3-3-2-6 5"/></svg>'
       . '<span class="media-placeholder__label"><b>' . htmlspecialchars($label) . '</b>Connected to CMS &middot; slot: ' . htmlspecialchars($key) . '</span>'
       . '</div>';
}
