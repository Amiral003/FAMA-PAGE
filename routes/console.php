<?php

use App\Models\Post;
use App\Models\Staff;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;


Schedule::command('posts:publish-scheduled')->everyMinute();

Schedule::command('backup:run --only-db')->dailyAt('02:00');
Schedule::command('backup:clean')->dailyAt('03:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('generate:sitemap', function () {

    $sitemap = Sitemap::create();

    // =========================
    // Pages statiques
    // =========================
    $staticPages = [
        '/' => 1.0,
        '/about' => 0.9,
        '/actualites' => 0.9,
        '/recrutement' => 0.8,
        '/phototheque' => 0.7,
        '/videotheque' => 0.7,
        '/com-ops' => 0.8,
        '/contact' => 0.6,
    ];

    foreach ($staticPages as $path => $priority) {
        $sitemap->add(
            Url::create('https://fama.mil.ml' . $path)
                ->setPriority($priority)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );
    }

    // =========================
    // Posts dynamiques
    // =========================
    Post::query()
        ->published()
        ->select([
            'slug',
            'updated_at',
        ])
        ->orderByDesc('updated_at')
        ->chunk(100, function ($posts) use ($sitemap) {

            foreach ($posts as $post) {

                $sitemap->add(
                    Url::create("https://fama.mil.ml/posts/{$post->slug}")
                        ->setLastModificationDate($post->updated_at)
                        ->setPriority(0.9)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                );
            }
        });

    // =========================
    // États-majors
    // =========================
    Staff::query()
        ->select([
            'slug',
            'updated_at',
            'order',
            'name',
        ])
        ->orderByRaw('"order" asc nulls last')
        ->orderBy('name')
        ->chunk(100, function ($staffs) use ($sitemap) {

            foreach ($staffs as $staff) {

                $sitemap->add(
                    Url::create("https://fama.mil.ml/etat-major/{$staff->slug}")
                        ->setLastModificationDate($staff->updated_at)
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            }
        });

    // =========================
    // Génération fichier XML
    // =========================
    $sitemap->writeToFile(public_path('sitemap.xml'));

    $this->info('Sitemap dynamique généré avec succès.');

})->purpose('Generate dynamic sitemap');
