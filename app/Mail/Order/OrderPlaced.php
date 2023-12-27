<?php

declare(strict_types=1);

namespace App\Mail\Order;

use App\Models\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * メッセージEnvelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '注文完了のお知らせ',
        );
    }

    /**
     * メッセージ内容
     */
    public function content(): Content
    {
        return new Content(
            html: 'mail.order.placed',
        );
    }
}
