<?php

namespace App\Mail;

use App\Models\StudentTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentTransferActioned extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  StudentTransfer  $transfer  The transfer (relationships pre-loaded)
     * @param  'accepted'|'rejected'|'cancelled'  $action
     */
    public function __construct(
        public readonly StudentTransfer $transfer,
        public readonly string $action,
    ) {}

    public function envelope(): Envelope
    {
        $verb = match ($this->action) {
            'accepted'  => 'Accepted',
            'rejected'  => 'Rejected',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->action),
        };

        $student = $this->transfer->student_name
            ? "({$this->transfer->student_name})"
            : '';

        $from = $this->transfer->fromInstitution?->name ?? 'Sending School';
        $to   = $this->transfer->toInstitution?->name   ?? 'Receiving School';

        return new Envelope(
            subject: "[FDE Portal] Transfer {$verb} — {$from} → {$to} {$student}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-transfer-actioned',
        );
    }
}
