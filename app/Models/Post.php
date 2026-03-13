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
    ];



    protected $casts = [
        'validated_at' => 'datetime',
        'published_at' => 'datetime',
    ];


    protected static function makeUniqueSlug(string $title): string
{
    $base = Str::slug($title);

    // fallback si titre vide ou caractères bizarres
    if ($base === '') {
        $base = 'post';
    }

    $slug = $base;
    $i = 2;

    // ✅ Postgres + index unique => on évite les collisions
    while (static::where('slug', $slug)->exists()) {
        $slug = $base . '-' . $i;
        $i++;
    }

    return $slug;
}

    // -------------------------------------------------
    // ✅ BOOTED : slug + statut par défaut (compatible avec ta contrainte CHECK)
    // -------------------------------------------------
    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            // 1) Slug auto
            
            // 1) Slug auto / slug manuel sécurisé
if (!empty($post->slug)) {
    // si Filament envoie un slug manuel : on le normalise + on garantit l'unicité
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

            // 2) Statut par défaut : brouillon
            // IMPORTANT: ta DB avait un DEFAULT "Brouillion" (ancien) -> ça casse le CHECK.
            // Ici on force en PHP pour éviter toute insertion incorrecte.
            if (empty($post->status)) {
                $post->status = self::STATUS_BROUILLON;
            }

            // 3) Type par défaut (si oublié)
            if (empty($post->type)) {
                $post->type = self::TYPE_ARTICLE;
            }
        });

        static::updating(function (Post $post) {
            // Si le titre change et que le slug est vide, on le régénère.
            // (On ne touche pas si slug déjà présent pour ne pas casser les URLs publiées)
            if ($post->isDirty('title') && empty($post->slug) && !empty($post->title)) {
                $post->slug = static::makeUniqueSlug($post->title);
            }
        });
    }

    public function dailyViews(): \Illuminate\Database\Eloquent\Relations\HasMany
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
        /**
     * ✅ Alias de compatibilité
     * Filament / anciens fichiers utilisent souvent $post->user.
     * On garde author() (plus clair), mais on fournit user() pour éviter les erreurs.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // -------------------------------------------------
    // ✅ SCOPES : simplifier API + queries
    // -------------------------------------------------
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLIE);
    }

    public function scopePublicOrder($query)
    {
        // Ordre stable pour le fil public :
        // 1) published_at (si défini)
        // 2) validated_at
        // 3) created_at
        return $query->orderByDesc('published_at')
            ->orderByDesc('validated_at')
            ->orderByDesc('created_at');
    }

    // -------------------------------------------------
    // ✅ WORKFLOW FINAL (TA VISION)
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
            'rejection_notes' => null, // une fois publié, on nettoie le motif
        ]);
    }

    /**
     * Rejeter pour correction (Validateur)
     * Règle: Le validateur rejette UNIQUEMENT les brouillons.
     * Résultat: status = revision + notes (visible en admin) + audit validator.
     */
    public function rejectForRevision(int $validatorId, string $notes): void
{
    // ✅ Autoriser brouillon ET publie
    if (! in_array($this->status, [
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

        // 🔴 IMPORTANT si le post était publié :
        // il ne doit plus apparaître sur le site public
        'published_at'    => null,
    ]);
}

    /**
     * Marquer corrigé (Rédacteur)
     * Règle: après correction, le rédacteur remet le post en brouillon
     * pour qu’il soit clairement "à traiter" côté validateur.
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
            // On ne supprime pas rejection_notes ici :
            // - soit tu la gardes pour historique
            // - soit tu la vides si tu veux (à toi de décider)
            // 'rejection_notes' => null,
        ]);
    }

    // -------------------------------------------------
    // ✅ Helpers utiles
    // -------------------------------------------------
    public function isPublic(): bool
    {
        return $this->status === self::STATUS_PUBLIE;
    }
}