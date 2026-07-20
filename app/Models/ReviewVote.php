<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewVote extends Model
{
    protected $fillable = [
        'user_id',
        'review_id',
        'vote_type',
    ];
}
