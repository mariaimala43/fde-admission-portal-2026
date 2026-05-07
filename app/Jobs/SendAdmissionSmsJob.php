<?php

namespace App\Jobs;

use App\Models\Admission;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdmissionSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Admission $admission)
    {
    }

    public function handle(SmsService $sms): void
    {
        // Ensure the school relation is loaded
        $admission = $this->admission->loadMissing('school');

        $anySent = false;

        try {
            // 1. Compose and send principal SMS
            $principalMessage = implode("\n", [
                "FDE Portal: New admission referral received.",
                "Child: {$admission->child_name}, Class: {$admission->class_name}",
                "Parent Contact: {$admission->parent_contact}",
                "Reference No: {$admission->ref_id}",
                "Please log in to FDE Portal to process.",
            ]);

            $principalResult = $sms->send($admission->school->principal_contact, $principalMessage);

            SmsLog::create([
                'admission_id'     => $admission->id,
                'recipient_type'   => 'principal',
                'phone_number'     => $admission->school->principal_contact,
                'message'          => $principalMessage,
                'status'           => $principalResult['success'] ? 'sent' : 'failed',
                'gateway_response' => $principalResult['response'] ?? null,
                'sent_at'          => $principalResult['success'] ? now() : null,
            ]);

            if ($principalResult['success']) {
                $anySent = true;
            }

            // 2. Compose and send parent SMS
            $parentMessage = implode("\n", [
                "Dear {$admission->parent_name}, your child {$admission->child_name} has been referred for admission.",
                "School: {$admission->school->name}",
                "Address: {$admission->school->address}",
                "Class: {$admission->class_name}",
                "Principal Contact: {$admission->school->principal_contact}",
                "Reference No: {$admission->ref_id}",
                "Please visit the school with original documents.",
            ]);

            $parentResult = $sms->send($admission->parent_contact, $parentMessage);

            SmsLog::create([
                'admission_id'     => $admission->id,
                'recipient_type'   => 'parent',
                'phone_number'     => $admission->parent_contact,
                'message'          => $parentMessage,
                'status'           => $parentResult['success'] ? 'sent' : 'failed',
                'gateway_response' => $parentResult['response'] ?? null,
                'sent_at'          => $parentResult['success'] ? now() : null,
            ]);

            if ($parentResult['success']) {
                $anySent = true;
            }

            // 3. If at least one SMS was sent, stamp sms_sent_at
            if ($anySent) {
                $admission->update(['sms_sent_at' => now()]);
            }
        } catch (\Exception $e) {
            // SMS failures must NOT fail the job
            Log::channel('nfemis_sync')->error('SendAdmissionSmsJob error', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}
