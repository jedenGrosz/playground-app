<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSync extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'counts' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
