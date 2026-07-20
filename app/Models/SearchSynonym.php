<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchSynonym extends Model
{
    use HasFactory;

    protected $fillable = [
        'term',
        'replace_with',
    ];
}
