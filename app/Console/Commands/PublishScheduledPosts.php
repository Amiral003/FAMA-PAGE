<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publie automatiquement les posts programmés arrivés à échéance';

    public function handle(): int
    {
        $posts = Post::query()
            ->where('status', Post::STATUS_PROGRAMME)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($posts as $post) {
            $post->update([
                'status'       => Post::STATUS_PUBLIE,
                'published_at' => now(),
                'scheduled_at' => null,
            ]);
        }

        $this->info($posts->count() . ' post(s) publié(s).');

        return self::SUCCESS;
    }
}