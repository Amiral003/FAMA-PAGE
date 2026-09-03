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

        @elseif(isset($seoAbout))
            <main>
                <header>
                    <p>République du Mali • Défense nationale</p>

                    <h1>Forces Armées Maliennes</h1>

                    <p>
                        Les Forces Armées Maliennes constituent l’outil de défense de
                        l’État. Elles participent à la protection de la souveraineté
                        nationale, à la défense du territoire, à la sécurisation des
                        populations et à l’exécution des missions militaires décidées
                        par les autorités compétentes.
                    </p>
                </header>

                <section>
                    <h2>Présentation générale</h2>

                    <p>
                        Les Forces Armées Maliennes regroupent des structures de
                        commandement, des directions, des services, des organismes
                        spécialisés et des entités d'appui chargés de la planification,
                        de la coordination, de l'administration, du soutien et de la
                        conduite des opérations.
                    </p>

                    <p>
                        Leur organisation repose sur la discipline, la continuité du
                        commandement, l'efficacité opérationnelle et le respect des
                        principes républicains.
                    </p>
                </section>

                <section>
                    <h2>Missions essentielles</h2>

                    <article>
                        <h3>Défense du territoire</h3>
                        <p>
                            Assurer l'intégrité du territoire national et contribuer à la
                            protection de la souveraineté de l'État.
                        </p>
                    </article>

                    <article>
                        <h3>Protection des populations</h3>
                        <p>
                            Participer à la sécurisation des populations, des institutions
                            et des espaces stratégiques.
                        </p>
                    </article>

                    <article>
                        <h3>Appui au commandement</h3>
                        <p>
                            Structurer, coordonner et soutenir les opérations à travers
                            les structures de commandement, directions et services.
                        </p>
                    </article>
                </section>

                <section>
                    <h2>Organisation institutionnelle</h2>

                    <p>
                        L'organisation institutionnelle de la défense comprend plusieurs
                        niveaux : ministère, état-major général, états-majors, directions,
                        services techniques, structures d'appui et organismes spécialisés.
                    </p>
                </section>

                @if($seoAbout['staffs']->isNotEmpty())
                    <section aria-labelledby="structures-institutionnelles">
                        <h2 id="structures-institutionnelles">
                            Structures institutionnelles
                        </h2>

                        <p>
                            Organisation hiérarchique de la Défense nationale et des Forces Armées.
                        </p>

                        <ul>
                            @foreach($seoAbout['staffs'] as $staff)
                                <li>
                                    <a href="{{ url('/etat-major/' . $staff->slug) }}">
                                        {{ $staff->name }}
                                    </a>

                                    @if($staff->initials)
                                        <span>({{ $staff->initials }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </main>

        @elseif(isset($seoHome))
            <main>
                <h1>Forces Armées Maliennes</h1>

                <p>
                    Portail officiel des Forces Armées Maliennes.
                </p>

                <nav aria-label="Navigation principale">
                    <ul>
                        <li><a href="{{ url('/about') }}">À propos</a></li>
                        <li><a href="{{ url('/actualites') }}">Actualités</a></li>
                        <li><a href="{{ url('/communiques') }}">Communiqués</a></li>
                        <li><a href="{{ url('/recrutement') }}">Recrutement</a></li>
                        <li><a href="{{ url('/phototheque') }}">Photothèque</a></li>
                        <li><a href="{{ url('/videotheque') }}">Vidéothèque</a></li>
                        <li><a href="{{ url('/etatmajor') }}">État-major</a></li>
                        <li><a href="{{ url('/contact') }}">Contact</a></li>
                    </ul>
                </nav>

                @if($seoHome['latestPosts']->isNotEmpty())
                    <section aria-labelledby="actualites-recentes">
                        <h2 id="actualites-recentes">Actualités récentes</h2>

                        <ul>
                            @foreach($seoHome['latestPosts'] as $post)
                                <li>
                                    <a href="{{ url('/posts/' . $post->slug) }}">
                                        {{ $post->title }}
                                    </a>

                                    @if($post->published_at)
                                        <time datetime="{{ $post->published_at->toIso8601String() }}">
                                            {{ $post->published_at->format('d/m/Y') }}
                                        </time>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </main>
        @endif
    </div>
</body>

</html>
