<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'channel',
        'subject',
        'body',
        'variables_guide',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables_guide' => 'array',
            'is_active'       => 'boolean',
        ];
    }
    
    /**
     * Parses the template by replacing {variables} with actual data.
     */
    public function parseTemplate(array $data): string
    {
        $parsed = $this->body;
        foreach ($data as $key => $value) {
            $parsed = str_replace('{' . $key . '}', (string) $value, $parsed);
        }
        return $parsed;
    }
}
