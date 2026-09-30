<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalUser extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'company' => 'array',
            'address' => 'array',
            'raw_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
