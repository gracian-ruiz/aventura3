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
        'is_read',
        'read_at',
        'payload',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $message): void {
            $direction = (string) ($message->direction ?? '');

            if ($direction === 'inbound') {
                $message->is_read = false;
                $message->read_at = null;
                return;
            }

            if ($direction === 'outbound' && $message->is_read === null) {
                $message->is_read = true;
            }

            if ($direction === 'outbound' && $message->is_read === true && $message->read_at === null) {
                $message->read_at = now();
            }
        });
    }

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