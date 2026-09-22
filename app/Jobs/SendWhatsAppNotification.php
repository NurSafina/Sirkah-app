<?php

namespace App\Jobs;

use App\Models\WhatsAppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WhatsAppNotification $notification) {}

    public int $tries = 3;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $this->notification->increment('attempts');
        $this->notification->refresh();

        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.token');
        if (blank($phoneNumberId) || blank($token)) {
            $this->notification->update(['status' => 'failed', 'error_message' => 'Provider WhatsApp belum dikonfigurasi.']);
            return;
        }

        try {
            Http::withToken($token)
                ->post('https://graph.facebook.com/' . config('services.whatsapp.api_version', 'v20.0') . '/' . $phoneNumberId . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $this->notification->phone_number,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $this->notification->message],
                ])
                ->throw();

            $this->notification->update(['status' => 'sent', 'sent_at' => now(), 'error_message' => null]);
        } catch (Throwable $exception) {
            $this->notification->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
        }
    }
}
