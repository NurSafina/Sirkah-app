<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_number',
        'name',
        'parent_name',
        'parent_phone',
        'card_token',
        'card_code',
        'card_issued_at',
        'card_revoked_at',
        'photo_path',
        'card_status',
        'classroom',
        'status',
        'balance',
        'daily_limit',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'daily_limit' => 'decimal:2',
        'card_status' => 'string',
        'card_issued_at' => 'datetime',
        'card_revoked_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function balanceMutations()
    {
        return $this->hasMany(BalanceMutation::class);
    }
}
