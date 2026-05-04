<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'email',
        'ip_address',
        'user_agent',
        'severity',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}