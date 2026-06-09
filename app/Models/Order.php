<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_SERVED = 'served';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'table_id',
        'order_number',
        'status',
        'subtotal',
        'tax',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'table_id' => 'integer',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
