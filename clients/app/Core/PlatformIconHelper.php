<?php

declare(strict_types=1);

namespace App\Core;

final class PlatformIconHelper
{
    /**
     * Load an SVG icon dynamically from public/assets/platform-icons
     * and output it as inline HTML, injecting any optional attributes.
     */
    public static function getSvg(string $name, string $attributes = ''): string
    {
        $path = BASE_PATH . '/public/assets/platform-icons/' . $name . '.svg';
        if (file_exists($path)) {
            $svg = file_get_contents($path);
            if ($attributes !== '') {
                $svg = str_replace('<svg ', '<svg ' . $attributes . ' ', $svg);
            }
            return $svg;
        }

        // Return a generic fallback icon if not found
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' . $attributes . ' class="lucide lucide-package"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>';
    }

    /**
     * Load an SVG icon at an exact pixel size, stripping any width/height
     * attributes declared inside the SVG file so CSS cannot be overridden.
     *
     * Use this for sidebar nav icons and any context where SVG internal
     * dimension declarations would override layout CSS.
     *
     * @param string $name       Icon slug (without .svg extension)
     * @param int    $size       Pixel size applied as width AND height (default 20)
     * @param string $extraAttrs Additional HTML attributes to inject onto <svg>
     */
    public static function getSvgSized(string $name, int $size = 20, string $extraAttrs = ''): string
    {
        $path = BASE_PATH . '/public/assets/platform-icons/' . $name . '.svg';
        if (file_exists($path)) {
            $svg = (string) file_get_contents($path);

            // Strip any hardcoded width/height so our style wins
            $svg = preg_replace('/<svg([^>]*)\swidth=["\'][^"\']*["\']/', '<svg$1', $svg) ?? $svg;
            $svg = preg_replace('/<svg([^>]*)\sheight=["\'][^"\']*["\']/', '<svg$1', $svg) ?? $svg;

            // Inject exact size via style attribute
            $style = 'style="width:' . $size . 'px;height:' . $size . 'px;'
                   . 'min-width:' . $size . 'px;flex-shrink:0;'
                   . 'display:inline-block;vertical-align:middle;"';
            $inject = $style . ($extraAttrs ? ' ' . $extraAttrs : '');

            $svg = str_replace('<svg ', '<svg ' . $inject . ' ', $svg);
            return $svg;
        }

        // Fallback when file not found
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"'
             . ' stroke="currentColor" stroke-width="2"'
             . ' style="width:' . $size . 'px;height:' . $size . 'px;display:inline-block;vertical-align:middle;"'
             . ' ' . $extraAttrs . '>'
             . '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>'
             . '</svg>';
    }
}
