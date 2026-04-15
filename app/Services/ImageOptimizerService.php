<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Image Optimization Pipeline
 *
 * - Resizes images to max width (never upscales)
 * - Converts to WebP via GD (quality 75)
 * - Strips EXIF metadata
 * - Preserves aspect ratio
 */
class ImageOptimizerService
{
    // Context-based max widths (never upscale beyond original)
    public const MAX_PRODUCT  = 600;
    public const MAX_CATEGORY = 500;
    public const MAX_HERO     = 1400;
    public const MAX_THUMB    = 400;

    private int $quality = 75;

    /**
     * Optimize an image already stored on the public disk.
     *
     * @param string $storagePath  Relative path on public disk (e.g. 'products/abc.jpg')
     * @param int    $maxWidth     Max width in px (uses class constants)
     * @return string  New relative path (WebP) — or original path if optimization fails
     */
    public function optimize(string $storagePath, int $maxWidth = self::MAX_PRODUCT): string
    {
        // Skip if already WebP
        if (str_ends_with(strtolower($storagePath), '.webp')) {
            return $storagePath;
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($storagePath)) {
            return $storagePath;
        }

        $absolutePath = $disk->path($storagePath);

        try {
            $imageData = @file_get_contents($absolutePath);
            if (!$imageData) return $storagePath;

            $src = @imagecreatefromstring($imageData);
            if (!$src) return $storagePath;

            $origW = imagesx($src);
            $origH = imagesy($src);

            // Never upscale
            $newW = min($origW, $maxWidth);
            $newH = (int) round($origH * ($newW / $origW));

            // Resize if needed
            if ($newW < $origW) {
                $dst = imagecreatetruecolor($newW, $newH);
                // Preserve transparency for PNG
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                imagedestroy($src);
                $src = $dst;
            }

            // Generate WebP path
            $pathInfo = pathinfo($storagePath);
            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

            // Save as WebP
            $webpAbsPath = $disk->path($webpPath);
            $dir = dirname($webpAbsPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $success = imagewebp($src, $webpAbsPath, $this->quality);
            imagedestroy($src);

            if ($success && file_exists($webpAbsPath)) {
                // Verify WebP is actually smaller
                $origSize = filesize($absolutePath);
                $webpSize = filesize($webpAbsPath);

                if ($webpSize < $origSize) {
                    return $webpPath;
                } else {
                    // WebP larger (rare) — keep original, delete WebP
                    @unlink($webpAbsPath);
                    return $storagePath;
                }
            }

            return $storagePath;
        } catch (\Throwable $e) {
            // Never break upload flow — return original
            return $storagePath;
        }
    }

    /**
     * Optimize an uploaded file before it's stored.
     * Call this AFTER ->store() to process the saved file.
     *
     * Usage:
     *   $path = $file->store('products', 'public');
     *   $path = app(ImageOptimizerService::class)->optimize($path, ImageOptimizerService::MAX_PRODUCT);
     */
}
