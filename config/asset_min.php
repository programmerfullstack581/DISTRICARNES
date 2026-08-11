<?php
/**
 * Asset serving helper: serves minified assets in production,
 * original files in development.
 *
 * Usage in HTML:
 *   <link rel="stylesheet" href="<?php echo asset_min('static/css/header_en_general.css'); ?>">
 *   <script src="<?php echo asset_min('static/js/header_actions.js'); ?>"></script>
 *
 * In production (when DC_DEBUG is not '1'):
 *   - Looks for static/css/header_en_general.min.css first
 *   - Falls back to static/css/header_en_general.css if .min doesn't exist
 *
 * In development (DC_DEBUG=1):
 *   - Always serves the original file for easier debugging
 */

function asset_min(string $path): string
{
    static $debug = null;
    if ($debug === null) {
        $debug = (getenv('DC_DEBUG') === '1');
    }

    if ($debug) {
        return asset($path);
    }

    $file = dirname(__DIR__) . '/' . ltrim($path, '/');
    $min  = $file . '.min';

    if (is_file($min)) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return asset($path . '.min.' . $ext);
    }

    return asset($path);
}
