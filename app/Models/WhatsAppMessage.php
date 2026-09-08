<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'user_id',
        'bike_id',
        'appointment_id',
        'wa_id',
        'from_phone',
        'to_phone',
        'direction',
        'message_type',
        'body',
        'status',
        'payload',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bike()
    {
        return $this->belongsTo(Bike::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}