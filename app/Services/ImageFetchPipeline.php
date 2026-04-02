<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Downloads remote product image URLs to local storage.
 * Retries up to 3 times; skips on failure.
 */
class ImageFetchPipeline
{
    public function fetch(string $url, int $retries = 3): ?string
    {
        $url = trim($url);
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $ext  = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
        $ext  = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? $ext : 'jpg';
        $name = 'products/'.Str::random(16).'.'.$ext;

        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            try {
                $response = Http::timeout(15)->get($url);
                if ($response->successful()) {
                    Storage::disk('public')->put($name, $response->body());

                    return $name;
                }
            } catch (\Throwable $e) {
                if ($attempt === $retries) {
                    return null;
                }
                sleep(1);
            }
        }

        return null;
    }
}
