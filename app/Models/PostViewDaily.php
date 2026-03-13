<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostViewDaily extends Model
{
    protected $table = 'post_views_daily';

    protected $fillable = [
        'post_id',
        'ip_hash',
        'country',
        'view_date',
        'hits',
    ];

    protected $casts = [
        'view_date' => 'date',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
