<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class Post extends Model
{
    // -------------------------------------------------
    // ✅ STATUTS (source de vérité)
    // -------------------------------------------------
    public const STATUS_BROUILLON = 'brouillon'; // À traiter / à valider
    public const STATUS_REVISION  = 'revision';  // Rejeté, à corriger par le rédacteur
    public const STATUS_PUBLIE    = 'publie';    // Visible publiquement
    public const STATUS_PROGRAMME = 'programme'; // Validé, en attente de publication automatique

    // -------------------------------------------------
    // ✅ TYPES (alignés avec ton CHECK constraint)
    // -------------------------------------------------
    public const TYPE_FLASH       = 'flash';
    public const TYPE_ARTICLE     = 'article';
    public const TYPE_RECRUTEMENT = 'recrutement';
    public const TYPE_PDF         = 'pdf';
    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'thumbnail',
        'content',
        'status',
        'user_id',
        'pdf_path',
        'file_path',
        'file_type',
        'validated_by',
        'validated_at',
        'published_at',
        'rejection_notes',
        'video_url',
        'video_platform',
        'video_thumbnail_url',
        'total_views',
        'unique_views',
        'scheduled_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    protected static function makeUniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'post';
        }

        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    // -------------------------------------------------
    // ✅ BOOTED : slug + statut par défaut
    // -------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (!empty($post->slug)) {
                $post->slug = Str::slug($post->slug);
                $base = $post->slug !== '' ? $post->slug : 'post';
                $slug = $base;
                $i = 2;

                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }

                $post->slug = $slug;
            } elseif (!empty($post->title)) {
                $post->slug = static::makeUniqueSlug($post->title);
            }

            if (empty($post->status)) {
                $post->status = self::STATUS_BROUILLON;
            }

            if (empty($post->type)) {
                $post->type = self::TYPE_ARTICLE;
            }
        });

        static::updating(function (Post $post) {
            if ($post->isDirty('title') && empty($post->slug) && !empty($post->title)) {
                $post->slug = static::makeUniqueSlug($post->title);
            }
        });
    }

    public function dailyViews(): HasMany
    {
        return $this->hasMany(\App\Models\PostViewDaily::class);
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    // -------------------------------------------------
    // ✅ RELATIONS
    // -------------------------------------------------
    public function media(): HasMany
    {
        return $this->hasMany(\App\Models\PostMedia::class)->orderBy('order');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // -------------------------------------------------
    // ✅ SCOPES
    // -------------------------------------------------
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLIE);
    }

    public function scopePublicOrder($query)
    {
        return $query->orderByDesc('published_at')
            ->orderByDesc('validated_at')
            ->orderByDesc('created_at');
    }

    // -------------------------------------------------
    // ✅ WORKFLOW
    // -------------------------------------------------

    /**
     * Publier (Validateur)
     * Règle: Le validateur publie UNIQUEMENT les brouillons.
     */
    public function publish(int $validatorId): void
    {
        if ($this->status !== self::STATUS_BROUILLON) {
            throw new LogicException('Seuls les brouillons peuvent être publiés.');
        }

        $this->update([
            'status'          => self::STATUS_PUBLIE,
            'validated_by'    => $validatorId,
            'validated_at'    => now(),
            'published_at'    => now(),
            'rejection_notes' => null,
        ]);
    }

    /**
     * ✅ PROGRAMMER (CORRIGÉ)
     * Permet de programmer un brouillon OU de reprogrammer un post déjà programmé
     */
    public function schedulePublication(int $validatorId, $scheduledAt): void
    {
        // ✅ Autoriser brouillon ET programme (reprogrammation)
        if (!in_array($this->status, [
            self::STATUS_BROUILLON,
            self::STATUS_PROGRAMME,
        ], true)) {
            throw new LogicException('Seuls les brouillons ou les posts déjà programmés peuvent être programmés/reprogrammés.');
        }

        $this->update([
            'status'          => self::STATUS_PROGRAMME,
            'validated_by'    => $validatorId,
            'validated_at'    => now(),
            'scheduled_at'    => $scheduledAt,
            'published_at'    => null,
            'rejection_notes' => null,
        ]);
    }

    /**
     * Rejeter pour correction (Validateur)
     * Règle: Le validateur rejette UNIQUEMENT les brouillons.
     * Résultat: status = revision + notes (visible en admin) + audit validator.
     */
    public function rejectForRevision(int $validatorId, string $notes): void
    {
        if (!in_array($this->status, [
            self::STATUS_BROUILLON,
            self::STATUS_PUBLIE,
        ], true)) {
            throw new LogicException(
                'Seuls les brouillons ou les posts publiés peuvent être rejetés pour révision.'
            );
        }

        $this->update([
            'status'          => self::STATUS_REVISION,
            'validated_by'    => $validatorId,
            'validated_at'    => now(),
            'rejection_notes' => $notes,
            'published_at'    => null,
        ]);
    }

    /**
     * Marquer corrigé (Rédacteur)
     * Règle: après correction, le rédacteur remet le post en brouillon
     * pour qu'il soit clairement "à traiter" côté validateur.
     */
    public function markFixed(int $authorId): void
    {
        if ($this->status !== self::STATUS_REVISION) {
            throw new LogicException('Seuls les posts en révision peuvent être remis en brouillon.');
        }

        if ((int) $this->user_id !== (int) $authorId) {
            throw new LogicException('Seul l’auteur peut remettre ce post en brouillon.');
        }

        $this->update([
            'status' => self::STATUS_BROUILLON,
        ]);
    }

    // -------------------------------------------------
    // ✅ Helpers
    // -------------------------------------------------
    public function isPublic(): bool
    {
        return $this->status === self::STATUS_PUBLIE;
    }
}
