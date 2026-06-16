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
    'name',
    'initials',
    'slug',
    'logo',
    'motto',
    'description',
    'missions',

    'leader_name',
    'leader_rank',
    'leader_photo',
    'leader_photos',
    'leader_word',

    'second_leader_name',
    'second_leader_rank',
    'second_leader_photo',
    'second_leader_word',

    'parent_staff_id',

    'order',

    'contact_email',
    'contact_phone',
    'contact_hotline',
    'contact_address',
    'contact_hours',
    'contact_map_url',
    'contact_socials',
];

protected $casts = [
    'order' => 'integer',
    'leader_photos' => 'array',
    'contact_socials' => 'array',
];

public function setLeaderPhotosAttribute($value): void
{
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
    }

    $photos = collect(is_array($value) ? $value : [])
        ->filter(fn ($photo) => is_string($photo) && trim($photo) !== '')
        ->map(fn ($photo) => trim($photo))
        ->unique()
        ->take(3)
        ->values()
        ->all();

    $this->attributes['leader_photos'] = $photos ? json_encode($photos) : null;
    $this->attributes['leader_photo'] = $photos[0] ?? null;
}



public function contactMessages()
{
    return $this->hasMany(ContactMessage::class);
}

public function parent()
{
    return $this->belongsTo(Staff::class, 'parent_staff_id');
}

public function children()
{
    return $this->hasMany(Staff::class, 'parent_staff_id');
}

}
