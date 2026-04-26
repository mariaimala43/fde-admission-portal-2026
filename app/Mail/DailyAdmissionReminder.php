<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyAdmissionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $schoolName,
        public readonly string $cutoffTime,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Action Required: Submit Today's Admission Data — {$this->schoolName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-admission-reminder',
        );
    }
}
