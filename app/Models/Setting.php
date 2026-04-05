<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Setting $setting) {
            $changes = $setting->getChanges();
            
            if (array_key_exists('value', $changes)) {
                if (auth()->check()) {
                    \App\Models\AuditLog::log(
                        'setting_updated', 
                        $setting, 
                        ['value' => $setting->getOriginal('value')], 
                        ['value' => $setting->value]
                    );
                }
            }
        });
    }
}
