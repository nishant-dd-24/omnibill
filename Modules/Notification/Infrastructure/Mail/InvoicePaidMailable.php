<?php

declare(strict_types=1);

namespace Modules\Notification\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Invoice\Domain\Models\Invoice;

class InvoicePaidMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Paid - '.$this->invoice->number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_paid',
        );
    }
}
