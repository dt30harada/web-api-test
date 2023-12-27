<?php

declare(strict_types=1);

namespace App\Models\Ebook;

use Illuminate\Database\Eloquent\Model;

/**
 * 電子書籍の形式
 */
final class EbookFormat extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $guard = ['*'];
}
