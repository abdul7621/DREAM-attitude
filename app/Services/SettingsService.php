<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    private const CACHE_KEY = 'commerce.settings.array';

    public function __construct(
        private readonly CacheRepository $cache
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function allMerged(): array
    {
        $defaults = Config::get('commerce', []);
        $db = $this->allFromDatabase();

        return array_replace_recursive($defaults, $db);
    }

    /**
     * @return array<string, mixed>
     */
    public function allFromDatabase(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        return $this->cache->rememberForever(self::CACHE_KEY, function (): array {
            $out = [];
            foreach (Setting::query()->cursor() as $row) {
                Arr::set($out, $row->key, $row->value);
            }

            return $out;
        });
    }

    public function get(string $dotKey, mixed $default = null): mixed
    {
        return Arr::get($this->allMerged(), $dotKey, $default);
    }

    /**
     * Return all settings under a given top-level group key.
     * e.g. group('store') returns ['name' => ..., 'email' => ...]
     *
     * @return array<string, mixed>
     */
    public function group(string $prefix): array
    {
        return Arr::get($this->allMerged(), $prefix, []);
    }

    public function set(string $dotKey, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $dotKey],
            ['value' => $value]
        );
        $this->flushCache();
    }

    public function forget(string $dotKey): void
    {
        Setting::query()->where('key', $dotKey)->delete();
        $this->flushCache();
    }

    public function flushCache(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    public function syncMany(array $dotKeyedValues): void
    {
        foreach ($dotKeyedValues as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        $this->flushCache();
    }
}
