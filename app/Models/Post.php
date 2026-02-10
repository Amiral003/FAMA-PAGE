<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LogicException;

class Post extends Model
{
    public const STATUS_BROUILLON = 'brouillon';
    public const STATUS_REVISION  = 'revision';
    public const STATUS_PUBLIE    = 'publie';

    protected $fillable = [
        'title',

        'slug',
        'type',
        'thumbnail',
        'content',
        'status',
        'user_id',
        'pdf_path',
        'validated_by',
        'validated_at',
        'published_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

   protected static function booted()
{
    static::creating(function ($post) {
        // Génération du slug
        if (empty($post->slug)) {
            $post->slug = Str::slug($post->title);
        }

        // FORCE le statut par défaut en minuscules
        // Cela évite que PostgreSQL n'utilise sa valeur par défaut "Brouillion"
        // qui fait planter la contrainte CHECK
        if (empty($post->status)) {
            $post->status = self::STATUS_BROUILLON;
        }
    });
}


    // ================= Relations =================

    public function media()
    {
return $this->hasMany(\App\Models\PostMedia::class)->orderBy('order');    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ================= Workflow =================

    public function submit(): void
    {
        if ($this->status !== self::STATUS_BROUILLON) {
            throw new LogicException('Seuls les brouillons peuvent être soumis.');
        }

        $this->update([
            'status' => self::STATUS_REVISION,
        ]);
    }

    // public function approve(int $validatorId): void
    // {
    //     if ($this->status !== self::STATUS_REVISION ) {
    //         throw new LogicException('Seuls les posts en révision peuvent être publiés.');
    //     }

    //     $this->update([
    //         'status' => self::STATUS_PUBLIE,
    //         'validated_by' => $validatorId,
    //         'published_at' => now(),
    //     ]);
    // }

    public function approve(int $validatorId): void
{
    // On définit les statuts autorisés pour la publication
    $allowedStatuses = [self::STATUS_REVISION, self::STATUS_BROUILLON];

    if (!in_array($this->status, $allowedStatuses)) {
        throw new LogicException('Seuls les brouillons ou les posts en révision peuvent être publiés.');
    }

    $this->update([
        'status'       => self::STATUS_PUBLIE,
        'validated_by' => $validatorId,
        'published_at' => now(),
        'validated_at' => now(), // Il est conseillé d'enregistrer aussi la date de validation
    ]);
}




    // Dans app/Models/Post.php

public function reject($userId)
{


    $this->update([
        'status' => self::STATUS_BROUILLON,
        // 'rejected_by' => $userId, (si tu as cette colonne)
    ]);
}
}
