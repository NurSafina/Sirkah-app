<?php

namespace App\Jobs;

use App\Models\WhatsAppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
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
        if (blank($phoneNumberId) || blank($token) || $token === 'isi_token_meta') {
            $this->notification->update([
                'status' => 'failed',
                'error_message' => 'Provider WhatsApp belum dikonfigurasi. Isi WHATSAPP_PHONE_NUMBER_ID dengan Phone Number ID Meta dan WHATSAPP_TOKEN dengan access token Meta.',
            ]);
            return;
        }

        try {
            $templateName = config('services.whatsapp.template_name');
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $this->notification->phone_number,
            ];

            if (filled($templateName)) {
                $payload['type'] = 'template';
                $payload['template'] = [
                    'name' => $templateName,
                    'language' => ['code' => config('services.whatsapp.template_language', 'id')],
                    'components' => [[
                        'type' => 'body',
                        'parameters' => [['type' => 'text', 'text' => $this->notification->message]],
                    ]],
                ];
            } else {
                $payload['type'] = 'text';
                $payload['text'] = ['preview_url' => false, 'body' => $this->notification->message];
            }

            Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->post('https://graph.facebook.com/' . config('services.whatsapp.api_version', 'v20.0') . '/' . $phoneNumberId . '/messages', $payload)
                ->throw();

            $this->notification->update(['status' => 'sent', 'sent_at' => now(), 'error_message' => null]);
        } catch (RequestException $exception) {
            $body = $exception->response?->json('error.message');
            $this->notification->update([
                'status' => 'failed',
                'error_message' => $body ?: $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            $this->notification->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
        }
    }
}
