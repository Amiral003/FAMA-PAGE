<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $seo['title'] ?? config('app.name', 'FAMA - Portail Officiel') }}</title>

    @if(isset($seo))
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="robots" content="index, follow, max-image-preview:large">

        <link rel="canonical" href="{{ $seo['canonical'] }}">

        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:type" content="{{ $seo['type'] }}">
        <meta property="og:url" content="{{ $seo['canonical'] }}">
        <meta property="og:site_name" content="Forces Armées Maliennes">
        <meta property="og:locale" content="fr_FR">

        <meta property="og:image" content="{{ $seo['image'] }}">
        <meta property="og:image:secure_url" content="{{ $seo['image'] }}">
        <meta property="og:image:alt" content="{{ $seo['title'] }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seo['title'] }}">
        <meta name="twitter:description" content="{{ $seo['description'] }}">
        <meta name="twitter:image" content="{{ $seo['image'] }}">
    @endif

    @if(isset($jsonLd))
        <script type="application/ld+json">
            {!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif

    <link rel="icon" type="image/png" href="/favicon.png">

    @vite('resources/js/app.js')
</head>

<body>
    <div id="app">
        @if(isset($seoPost))
            <main>
                <article>
                    <h1>{{ $seoPost['title'] }}</h1>

                    @if($seoPost['published_at'])
                        <time datetime="{{ $seoPost['published_at']->toIso8601String() }}">
                            {{ $seoPost['published_at']->format('d/m/Y') }}
                        </time>
                    @endif

                    <p>{{ $seoPost['content'] }}</p>
                </article>
            </main>

        @elseif(isset($seoCollection))
            <main>
                <h1>{{ $seoCollection['title'] }}</h1>

                <p>{{ $seoCollection['description'] }}</p>

                <section aria-label="Documents officiels de recrutement">
                    @foreach($seoCollection['items'] as $item)
                        <article>
                            <h2>
                                <a href="{{ $item['article_url'] }}">
                                    {{ $item['title'] }}
                                </a>
                            </h2>

                            @if($item['published_at'])
                                <time datetime="{{ $item['published_at']->toIso8601String() }}">
                                    {{ $item['published_at']->format('d/m/Y') }}
                                </time>
                            @endif

                            @if($item['excerpt'])
                                <p>{{ $item['excerpt'] }}</p>
                            @endif

                            <p>
                                <a href="{{ $item['pdf_url'] }}">
                                    Télécharger le document PDF officiel
                                </a>
                            </p>
                        </article>
                    @endforeach
                </section>
            </main>
        @endif
    </div>
</body>

</html>
