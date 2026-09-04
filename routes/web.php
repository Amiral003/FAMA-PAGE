<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Models\Post;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PublicPostPageController;
use App\Http\Controllers\PublicRecruitmentPageController;
use App\Http\Controllers\PublicAboutPageController;
use App\Http\Controllers\PublicStaffPageController;
use App\Http\Controllers\PublicNewsPageController;
use App\Http\Controllers\PublicPhotoGalleryPageController;
use App\Http\Controllers\PublicVideoGalleryPageController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Auth\TwoFactorSetupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Routes de fichiers
Route::get('/files/{post}', [FileController::class, 'show'])
    ->middleware('throttle:files')
    ->name('files.show');
// 2. Gestion du conflit Jetstream / Filament
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/admin');
    })->name('dashboard');
});

// Force le login vers Filament
Route::redirect('/login', '/admin/login');

// 3. Route de changement forcé du mot de passe
Route::middleware('auth')->group(function () {
    Route::get('/force-change-password', [ForcePasswordChangeController::class, 'edit'])
        ->name('password.force.change');

    Route::post('/force-change-password', [ForcePasswordChangeController::class, 'update'])
        ->name('password.force.update');
    Route::get('/setup-2fa', [TwoFactorSetupController::class, 'show'])
    ->name('two-factor.setup');
});

// 4. Route d'accueil : SEO serveur + SPA Vue
Route::get('/', function () {
    $latestPosts = Cache::remember('seo:home:latest-posts', 60, function () {
        return Post::query()
            ->published()
            ->where('type', Post::TYPE_ARTICLE)
            ->whereNotNull('slug')
            ->publicOrder()
            ->limit(8)
            ->get(['title', 'slug', 'published_at']);
    });

    $seo = [
        'title' => 'Forces Armées Maliennes - Portail officiel',
        'description' => 'Portail officiel des Forces Armées Maliennes : actualités, informations institutionnelles, communiqués, recrutement et publications officielles.',
        'canonical' => url('/'),
        'type' => 'website',
        'image' => url('/images/og-default.jpg'),
    ];

    $seoHome = [
        'latestPosts' => $latestPosts,
    ];

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'Forces Armées Maliennes',
        'url' => url('/'),
        'inLanguage' => 'fr',
    ];

    return view('front', compact('seo', 'seoHome', 'jsonLd'));
});

// À propos : SEO serveur + SPA Vue
Route::get('/about', PublicAboutPageController::class)
    ->name('public.about');

Route::get('/etat-major/{slug}', PublicStaffPageController::class)
    ->name('public.staff.show');

Route::get('/actualites', PublicNewsPageController::class)
    ->name('public.news');

Route::get('/phototheque', PublicPhotoGalleryPageController::class)
    ->name('public.photos');

Route::get('/videotheque', PublicVideoGalleryPageController::class)
    ->name('public.videos');

Route::redirect('/communiques', '/actualites', 301);
Route::redirect('/portfolio', '/actualites', 301);

// Page publique d'un article : SEO serveur + SPA Vue
Route::get('/posts/{slug}', PublicPostPageController::class)
    ->name('public.posts.show');

// Recrutement & concours : SEO serveur + SPA Vue
Route::get('/recrutement', PublicRecruitmentPageController::class)
    ->name('public.recruitment');

// Les faux chemins de sitemap ne doivent pas être capturés par la SPA.
Route::get('/sitemap_index.xml', fn () => abort(404));
Route::get('/sitemap/sitemap.xml', fn () => abort(404));

// 5. Capture du Front-end SPA

Route::view('/{any}', 'front')
    ->where('any', '^(?!admin|livewire|force-change-password|setup-2fa|user).*$');
