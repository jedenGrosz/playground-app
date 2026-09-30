<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalApiSnapshot extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'fetched_at' => 'datetime',
        ];
    }
}
