<?php

namespace App\Support;

final class Installation
{
    public static function lockPath(): string
    {
        return config('installer.lock_file');
    }

    public static function isInstalled(): bool
    {
        return is_file(self::lockPath());
    }

    public static function assertWritablePaths(): array
    {
        $paths = [
            storage_path(),
            storage_path('app'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        $notWritable = [];
        foreach ($paths as $path) {
            if (! is_dir($path) && ! @mkdir($path, 0755, true) && ! is_dir($path)) {
                $notWritable[] = $path.' (missing)';

                continue;
            }
            if (! is_writable($path)) {
                $notWritable[] = $path;
            }
        }

        return $notWritable;
    }
}
