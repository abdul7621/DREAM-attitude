<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $fillable = [
        'folder',
        'filename',
        'path',
        'alt_text',
        'size_bytes',
        'mime_type',
    ];

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
