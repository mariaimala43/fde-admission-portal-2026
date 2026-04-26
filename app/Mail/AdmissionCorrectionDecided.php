<?php

namespace App\Mail;

use App\Models\AdmissionCorrection;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdmissionCorrectionDecided extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  AdmissionCorrection  $correction  The correction (relationships pre-loaded)
     * @param  'approved'|'rejected'  $decision
     */
    public function __construct(
        public readonly AdmissionCorrection $correction,
        public readonly string $decision,
    ) {}

    public function envelope(): Envelope
    {
        $verb    = $this->decision === 'approved' ? 'Approved' : 'Rejected';
        $school  = $this->correction->institution?->name ?? 'Your School';
        $date    = $this->correction->admission_date?->format('d M Y') ?? '';
        $class   = $this->correction->classModel?->name ?? '';

        return new Envelope(
            subject: "[FDE Portal] Correction {$verb} — {$school} | {$class} | {$date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admission-correction-decided',
        );
    }
}
