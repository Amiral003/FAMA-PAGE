<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostRejectedNotification extends Notification
{
    use Queueable;

    public $post;
    public $notes;
    public $validator;

    public function __construct($post, $notes, $validator = null)
    {
        $this->post = $post;
        $this->notes = $notes;
        $this->validator = $validator;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'format' => 'filament',
            'status' => 'danger',

            // ✅ court
            'title' => 'Publication renvoyée',

            // ✅ message plus court (pas tout le titre)
            'body' => 'Corrections demandées sur votre publication.',

            // ✅ infos extra
            'post_title' => $this->post->title,
            'validator_name' => $this->validator?->name ?? 'Validateur',
            'notes' => $this->notes,

            // ✅ redirection
            'url' => route('filament.admin.resources.posts.edit', $this->post),
        ];
    }
}