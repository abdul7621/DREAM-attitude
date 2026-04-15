<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class SlugService
{
    public function forProduct(string $title, ?int $ignoreId = null): string
    {
        return $this->uniqueSlug($title, fn (string $slug) => Product::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists());
    }

    public function forCategory(string $title, ?int $ignoreId = null): string
    {
        return $this->uniqueSlug($title, fn (string $slug) => Category::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists());
    }

    public function unique(string $title, string $table, ?int $ignoreId = null): string
    {
        return $this->uniqueSlug($title, fn (string $slug) => \Illuminate\Support\Facades\DB::table($table)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists());
    }

    private function uniqueSlug(string $title, callable $exists): string
    {
        $base = Str::slug($title) ?: 'item';
        $slug = $base;
        $i = 2;
        while ($exists($slug)) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
