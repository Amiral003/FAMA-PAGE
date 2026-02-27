<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostRejectedNotification extends Notification
{
    use Queueable;

    public $post;
    public $notes;

    public function __construct($post, $notes)
    {
        $this->post = $post;
        $this->notes = $notes;
    }

    public function via($notifiable)
    {
        return ['database']; // stockage en base
    }

    public function toDatabase($notifiable): array
{
    return [
        'format' => 'filament', // ⭐ OBLIGATOIRE
        'title' => 'Post renvoyé en révision',
        'body' => 'Votre publication "' . $this->post->title . '" nécessite des corrections.',
        'notes' => $this->notes,
        'post_id' => $this->post->id,
        'url' => route(
            'filament.admin.resources.posts.edit',
            $this->post
        ),
    ];
}
}