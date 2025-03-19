<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'user_id',
        'table',
        'order_type',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes'
    ];
    protected $casts = [
        'subtotal'=>'decimal:2',
        'discount'=>'decimal:2',
        'tax'=>'decimal:2',
        'total'=>'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): Hasmany
    {
        return $this->hasmany(OrderItem::class);
    }
}
