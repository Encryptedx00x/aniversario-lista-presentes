<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Asset URL builder with cache-busting query string.
 *
 * Appends `?v=<mtime>` to local assets so browsers/CDNs can cache the
 * file aggressively but instantly fetch a fresh copy whenever the file
 * on disk changes (no 24h-old JS biting us in production).
 *
 * Usage in views:
 *   <link rel="stylesheet" href="<?= \App\Helpers\Asset::url('/assets/css/app.css') ?>">
 *   <script src="<?= \App\Helpers\Asset::url('/assets/js/app.js') ?>"></script>
 */
class Asset {
    public static function url(string $publicPath): string {
        $clean    = '/' . ltrim($publicPath, '/');
        $diskPath = dirname(__DIR__, 2) . '/public' . $clean;
        $version  = is_file($diskPath) ? (string) filemtime($diskPath) : '0';
        return $clean . '?v=' . $version;
    }
}
