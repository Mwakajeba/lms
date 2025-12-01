<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MicrofinanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subject;
    public $recipientName;
    public $companyName;

    /**
     * Create a new message instance.
     */
    public function __construct($content = null, $subject = null, $recipientName = null, $companyName = null)
    {
        $this->content = $content ?? "Thank you for choosing our microfinance services.";
        $this->subject = $subject ?? "Important Information from " . config('app.name');
        $this->recipientName = $recipientName ?? "Valued Customer";
        $this->companyName = $companyName ?? config('app.name');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.microfinance',
            with: [
                'content' => $this->content,
                'recipientName' => $this->recipientName,
                'companyName' => $this->companyName,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            // You can add attachments here later
        ];
    }
} 