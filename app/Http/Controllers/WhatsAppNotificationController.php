<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsAppNotification;
use App\Models\WhatsAppNotification;

class WhatsAppNotificationController extends Controller
{
    public function index()
    {
        $notifications = WhatsAppNotification::with(['student', 'transaction'])->latest()->paginate(30);
        return view('whatsapp-notifications.index', compact('notifications'));
    }

    public function retry(WhatsAppNotification $notification)
    {
        $notification->update(['status' => 'pending', 'error_message' => null]);
        SendWhatsAppNotification::dispatch($notification);
        return back()->with('success', 'Notifikasi dijadwalkan untuk dikirim ulang.');
    }
}
