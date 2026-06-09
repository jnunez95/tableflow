<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Table extends Model
{
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_OCCUPIED = 'occupied';

    public const STATUS_RESERVED = 'reserved';

    protected $fillable = [
        'uuid',
        'number',
        'capacity',
        'status',
        'qr_code',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'capacity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Table $table) {
            if (empty($table->uuid)) {
                $table->uuid = static::generateUniqueUuid();
            }

            if (empty($table->qr_code)) {
                $table->qr_code = Str::uuid()->toString();
            }
        });
    }

    public static function generateUniqueUuid(): string
    {
        do {
            $uuid = strtoupper(Str::random(6));
        } while (static::query()->where('uuid', $uuid)->exists());

        return $uuid;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
