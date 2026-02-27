<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staffs';
    
    protected $fillable = [
    'name', 'initials', 'slug', 'logo', 'motto', 
    'description', 'missions', 
    'leader_name', 'leader_rank', 'leader_photo', 'leader_word', 
    'order'
];
}

