<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Post extends Model
{
     public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'content',
        'file_path',
        'status',
        'user_id',
        'validated_by',
        'validated_at',
    ];

    protected $casts = ['validated_at' => 'datetime',];
    public function user()
{
    return $this->belongsTo(User::class);
}

public function submit(User $user): void
{
    if ($this->status !== self::STATUS_DRAFT) {
        throw new LogicException('Seuls les brouillons peuvent être soumis.');
    }

    $this->update([
        'status' => self::STATUS_PENDING,
    ]);
}

public function approve(User $user): void
{
    if ($this->status !== self::STATUS_PENDING) {
        throw new LogicException('Seuls les posts en attente peuvent être approuvés.');
    }

    $this->update([
        'status' => self::STATUS_APPROVED,
        'validated_by' => $user->id,
        'validated_at' => now(),
    ]);
}

public function reject(User $user): void
{
    if ($this->status !== self::STATUS_PENDING) {
        throw new LogicException('Seuls les posts en attente peuvent être rejetés.');
    }

    $this->update([
        'status' => self::STATUS_REJECTED,
        'validated_by' => $user->id,
        'validated_at' => now(),
    ]);
}

   


}

