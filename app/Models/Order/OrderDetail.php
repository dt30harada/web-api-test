<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 注文の詳細
 */
final class OrderDetail extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [
        'id',
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * この注文詳細を所有する注文を取得する
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
