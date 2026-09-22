<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppNotification extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notifications';

    protected $fillable = [
        'student_id', 'transaction_id', 'phone_number', 'message', 'status',
        'attempts', 'sent_at', 'error_message', 'message_hash',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function student() { return $this->belongsTo(Student::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
