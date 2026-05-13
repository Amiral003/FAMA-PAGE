<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ContactMessage;

class Staff extends Model
{
    // ✅ Evite les requêtes N+1
    // protected $with = ['staffs'];
    protected $table = 'staffs';
    
    protected $fillable = [
    'name', 'initials', 'slug', 'logo', 'motto', 
    'description', 'missions', 
    'leader_name', 'leader_rank', 'leader_photo', 'leader_word', 
    'order','contact_email',
'contact_phone',
'contact_hotline',
'contact_address',
'contact_hours',
'contact_map_url',
'contact_socials'
];

protected $casts = [
    'order' => 'integer',
    'contact_socials' => 'array',
];



public function contactMessages()
{
    return $this->hasMany(ContactMessage::class);
}

}

