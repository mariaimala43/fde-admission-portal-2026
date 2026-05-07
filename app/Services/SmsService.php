<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): array
    {
        $gateway = config('services.sms.gateway', 'zong');

        return match ($gateway) {
            'zong'    => $this->sendViaZong($phone, $message),
            'jazz'    => $this->sendViaJazz($phone, $message),
            'infobip' => $this->sendViaInfobip($phone, $message),
            'twilio'  => $this->sendViaTwilio($phone, $message),
            default   => ['success' => false, 'response' => "Unknown gateway: {$gateway}"],
        };
    }

    private function sendViaZong(string $phone, string $message): array
    {
        // Zong Pakistan SMS Gateway (HTTP API)
        // TODO: Confirm exact endpoint and parameters with Zong account manager
        // Typical Zong SMSGW API format — update once credentials received
        try {
            $response = Http::timeout(10)->post(config('services.sms.zong_api_url'), [
                'username' => config('services.sms.zong_username'),
                'password' => config('services.sms.zong_password'),
                'senderId' => config('services.sms.zong_sender'),
                'to'       => $phone,
                'message'  => $message,
            ]);

            $success = $response->successful();
            return [
                'success'  => $success,
                'response' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::channel('nfemis_sync')->error('Zong SMS failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'response' => $e->getMessage()];
        }
    }

    private function sendViaJazz(string $phone, string $message): array
    {
        // TODO: Implement Jazz/Warid SMS gateway
        Log::channel('nfemis_sync')->info('Jazz SMS stub called', ['phone' => $phone]);
        return ['success' => false, 'response' => 'Jazz gateway not implemented yet'];
    }

    private function sendViaInfobip(string $phone, string $message): array
    {
        // TODO: Implement Infobip SMS gateway
        Log::channel('nfemis_sync')->info('Infobip SMS stub called', ['phone' => $phone]);
        return ['success' => false, 'response' => 'Infobip gateway not implemented yet'];
    }

    private function sendViaTwilio(string $phone, string $message): array
    {
        try {
            $sid   = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from  = config('services.twilio.from');

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To'   => $phone,
                    'Body' => $message,
                ]);

            return ['success' => $response->successful(), 'response' => $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'response' => $e->getMessage()];
        }
    }
}
