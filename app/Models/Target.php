<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = ['type', 'key', 'target_value', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];
}
