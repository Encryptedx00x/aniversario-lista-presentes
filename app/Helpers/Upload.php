<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Upload security scaffold (SEC-05).
 * Phase 1 provides validation helpers only — no file move logic yet.
 * Phase 3+ will add the full upload flow using these methods as gates.
 */
class Upload {
    /**
     * Validate the real MIME type of an uploaded file using libmagic.
     * NEVER trust $_FILES['type'] — it is user-supplied and trivially spoofed.
     *
     * @param string   $tmpPath      Value of $_FILES['field']['tmp_name']
     * @param string[] $allowedMimes e.g. ['image/jpeg', 'image/png', 'image/webp']
     */
    public static function validateMime(string $tmpPath, array $allowedMimes): bool {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return false; // fail closed — deny upload if finfo unavailable
        }
        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        return is_string($mimeType) && in_array($mimeType, $allowedMimes, strict: true);
    }

    /**
     * Validate the file extension against a whitelist.
     * Use alongside validateMime() — extension alone is insufficient.
     *
     * @param string   $filename  Original filename from $_FILES['field']['name']
     * @param string[] $whitelist e.g. ['jpg', 'jpeg', 'png', 'webp']
     */
    public static function validateExtension(string $filename, array $whitelist): bool {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $whitelist, strict: true);
    }
}
