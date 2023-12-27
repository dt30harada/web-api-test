<?php

declare(strict_types=1);

namespace App\Exceptions\Order;

use Illuminate\Http\JsonResponse;

/**
 * すでに注文済みの電子書籍を注文した場合の例外
 */
final class AlreadyOrderedException extends \RuntimeException
{
    protected $code = JsonResponse::HTTP_CONFLICT;

    protected $message = 'You have already ordered one of the selected ebooks.';

    /**
     * 例外をHTTPレスポンスとしてレンダリングする
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'You have already ordered one of the selected ebooks.',
        ], $this->getCode());
    }
}
