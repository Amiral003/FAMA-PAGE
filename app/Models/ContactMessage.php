<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    // ✅ IMPORTANT: force la bonne table
    protected $table = 'contact_messages';

    protected $fillable = [
        'staff_id',
        'name',
        'email',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent',
    ];

    // ✅ Evite N+1
    protected $with = ['staff'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}