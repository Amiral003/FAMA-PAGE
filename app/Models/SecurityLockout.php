<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLockout extends Model
{
    protected $fillable = [
        'email',
        'ip_address',
        'reason',
        'severity',
        'locked_until',
        'metadata',
    ];

    protected $casts = [
        'locked_until' => 'datetime',
        'metadata' => 'array',
    ];
}
