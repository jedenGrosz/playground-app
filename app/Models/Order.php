<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'discounted_total' => 'decimal:2',
            'raw_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function externalUser(): BelongsTo
    {
        return $this->belongsTo(ExternalUser::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
