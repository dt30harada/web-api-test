<?php

declare(strict_types=1);

namespace App\Models\Ebook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 電子書籍
 */
final class Ebook extends Model
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
     * この電子書籍の形式を取得する
     */
    public function ebookFormat(): BelongsTo
    {
        return $this->belongsTo(EbookFormat::class);
    }
}
