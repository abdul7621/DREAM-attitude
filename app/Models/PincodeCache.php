<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PincodeCache extends Model
{
    protected $fillable = ['postal_code', 'city', 'state'];
}
