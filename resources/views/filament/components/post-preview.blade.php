<div class="preview-container" style="background: #f8fafc; padding: 20px; font-family: 'Inter', sans-serif; min-height: 100vh;">
    <div class="main-card" style="background: white; border-radius: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #eef2ff; overflow: hidden; max-width: 900px; margin: auto;">

        {{-- Header : Tag & Date --}}
        <div style="padding: 40px 40px 20px 40px;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                @if($post->type === 'video')
                    <span style="background: #e0f2fe; color: #0284c7; padding: 6px 14px; border-radius: 50px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                        <svg style="width: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        VIDÉO OFFICIELLE
                    </span>
                @elseif($post->pdf_path)
                    <span style="background: #fee2e2; color: #dc2626; padding: 6px 14px; border-radius: 50px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px;">
                        DOCUMENT OFFICIEL
                    </span>
                @else
                    <span style="background: #dcfce7; color: #14B82C; padding: 6px 14px; border-radius: 50px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px;">
                        ACTUALITÉ
                    </span>
                @endif

                <span style="color: #64748b; font-size: 13px; display: flex; align-items: center; gap: 5px;">
                    <svg style="width: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $post->published_at ? $post->published_at->diffForHumans() : $post->created_at->diffForHumans() }}
                </span>
            </div>

            <h1 style="font-size: 2.3rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin: 0; letter-spacing: -0.02em;">
                {{ $post->title }}
            </h1>
        </div>

        {{-- Zone Media : Gestion des images ET des vidéos --}}
        @php
            // ✅ Extraction de l'ID YouTube depuis différents formats d'URL
            $youtubeId = null;
            if($post->type === 'video' && $post->video_url) {
                $url = $post->video_url;

                // Format: youtube.com/watch?v=XXXXXX
                if(preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
                    $youtubeId = $matches[1];
                }
                // Format: youtu.be/XXXXXX
                elseif(preg_match('/youtu\.be\/([^\/\?]+)/', $url, $matches)) {
                    $youtubeId = $matches[1];
                }
                // Format: youtube.com/embed/XXXXXX
                elseif(preg_match('/embed\/([^\/\?]+)/', $url, $matches)) {
                    $youtubeId = $matches[1];
                }
                // Format: youtube.com/shorts/XXXXXX
                elseif(preg_match('/shorts\/([^\/\?]+)/', $url, $matches)) {
                    $youtubeId = $matches[1];
                }
            }

            $isVideo = $post->type === 'video';
            $isMp4Video = $isVideo && $post->video_platform === 'mp4';

            // Collection des images
            $allMedia = collect();
            if($post->thumbnail) $allMedia->push(['type' => 'image', 'path' => $post->thumbnail]);
            if($post->media) {
                foreach($post->media as $m) {
                    $allMedia->push(['type' => 'image', 'path' => $m->file_path]);
                }
            }
        @endphp

        {{-- Affichage Vidéo YouTube --}}
        @if($isVideo && $youtubeId)
            <div style="background: #0f172a; position: relative;">
                <div style="aspect-ratio: 16/9;">
                    <iframe
                        src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1&autoplay=0"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        loading="lazy"
                        style="width: 100%; height: 100%;"
                        title="{{ $post->title }}">
                    </iframe>
                </div>
            </div>

        {{-- Affichage Vidéo MP4 (fichier local) --}}
        @elseif($isVideo && $isMp4Video && $post->video_url)
            <div style="background: #0f172a; position: relative;">
                <video
                    controls
                    playsinline
                    preload="metadata"
                    style="width: 100%; height: auto; display: block;"
                    poster="{{ $post->video_thumbnail_url ? Storage::disk('public')->url($post->video_thumbnail_url) : '' }}">
                    <source src="{{ filter_var($post->video_url, FILTER_VALIDATE_URL) ? $post->video_url : Storage::disk('public')->url($post->video_url) }}" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture vidéo.
                </video>
            </div>

        {{-- Affichage Galerie Images --}}
        @elseif($allMedia->count() > 0)
            <div style="background: #f8fafc; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; position: relative;">

                <div x-data="{ currentIndex: 0, images: {{ json_encode($allMedia->values()->toArray()) }} }" style="position: relative;">

                    {{-- Compteur --}}
                    @if($allMedia->count() > 1)
                    <div style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.6); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 10; backdrop-filter: blur(4px);">
                        <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span> photos
                    </div>
                    @endif

                    {{-- Image principale avec navigation --}}
                    <div style="position: relative; min-height: 480px; display: flex; align-items: center; justify-content: center; background: #fdfdfd;">

                        {{-- Bouton précédent --}}
                        @if($allMedia->count() > 1)
                        <button @click="currentIndex = (currentIndex - 1 + images.length) % images.length"
                                style="position: absolute; left: 15px; background: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; z-index: 10;">
                            <svg style="width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        @endif

                        <img :src="'/storage/' + images[currentIndex]['path']"
                             style="max-width: 100%; max-height: 480px; object-fit: contain; transition: all 0.3s ease;"
                             :alt="'Image ' + (currentIndex + 1)">

                        {{-- Bouton suivant --}}
                        @if($allMedia->count() > 1)
                        <button @click="currentIndex = (currentIndex + 1) % images.length"
                                style="position: absolute; right: 15px; background: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; z-index: 10;">
                            <svg style="width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        @endif
                    </div>

                    {{-- Miniatures --}}
                    @if($allMedia->count() > 1)
                    <div style="display: flex; gap: 10px; padding: 15px; overflow-x: auto; background: white; justify-content: center; flex-wrap: wrap;">
                        <template x-for="(img, idx) in images" :key="idx">
                            <div @click="currentIndex = idx"
                                 :style="'width: 70px; height: 70px; border-radius: 12px; overflow: hidden; border: 2px solid ' + (currentIndex === idx ? '#14B82C' : '#e2e8f0') + '; cursor: pointer; flex-shrink: 0; transition: all 0.2s;'"
                                 class="hover:opacity-80">
                                <img :src="'/storage/' + img['path']" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </template>
                    </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Message si aucun média n'est présent --}}
            <div style="background: #f1f5f9; padding: 80px 40px; text-align: center; color: #64748b;">
                <svg style="width: 48px; margin: 0 auto 16px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p>Aucune image ou vidéo associée à ce post.</p>
            </div>
        @endif

        {{-- Corps du texte --}}
        <div style="padding: 40px;">
            <div class="rich-content-preview" style="font-size: 1.15rem; line-height: 1.8; color: #334155;">
                {!! $post->content !!}
            </div>

            <style>
                .rich-content-preview strong, .rich-content-preview b { font-weight: 800; color: #0f172a; }
                .rich-content-preview ul { list-style-type: disc; margin-left: 20px; margin-bottom: 20px; }
                .rich-content-preview ol { list-style-type: decimal; margin-left: 20px; margin-bottom: 20px; }
                .rich-content-preview p { margin-bottom: 15px; }
                .rich-content-preview blockquote { border-left: 4px solid #14B82C; padding-left: 15px; font-style: italic; color: #64748b; margin: 20px 0; }
                .rich-content-preview img { max-width: 100%; border-radius: 16px; margin: 20px 0; }
                .rich-content-preview iframe { max-width: 100%; border-radius: 16px; margin: 20px 0; }
            </style>

            {{-- Bloc PDF --}}
            @if($post->pdf_path)
                <div style="margin-top: 40px; background: linear-gradient(135deg, #f8fafc, #ffffff); border: 1px solid #e2e8f0; border-radius: 20px; padding: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="background: #fee2e2; padding: 12px; border-radius: 14px;">
                            <svg style="width: 28px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p style="margin: 0; font-weight: 800; color: #0f172a; font-size: 1rem;">Document officiel joint</p>
                            <p style="margin: 0; font-size: 0.85rem; color: #64748b;">PDF publié par les FAMa</p>
                        </div>
                    </div>
                    <a href="{{ Storage::disk('public')->url($post->pdf_path) }}"
                       target="_blank"
                       style="background: #0f172a; color: white; padding: 10px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.85rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
                        <svg style="width: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Ouvrir le PDF
                    </a>
                </div>
            @endif

            {{-- Signature --}}
            <div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #eef2ff; text-align: right;">
                <div style="width: 60px; height: 4px; background: #14B82C; margin-left: auto; margin-bottom: 15px; border-radius: 2px;"></div>
                <p style="font-size: 1.3rem; font-weight: 900; color: #1e293b; text-transform: uppercase; margin: 0;">
                    LA RÉDACTION
                </p>
                <p style="font-size: 0.85rem; color: #64748b; margin: 5px 0 0 0;">
                    Direction de l'Information et des Relations Publiques des Armées
                </p>
            </div>

            {{-- Validateur --}}
            @if($post->validator)
                <div style="margin-top: 25px; background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 16px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg style="width: 18px; color: #16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span style="font-size: 0.85rem; color: #166534; font-weight: 600;">Validé par</span>
                    </div>
                    <span style="font-weight: 800; color: #14532d;">{{ $post->validator->name }}</span>
                </div>
            @endif

            {{-- Statistiques d'audience --}}
            <div style="margin-top: 25px; background: #ffffff; border: 1px solid #eef2ff; border-radius: 16px; padding: 15px 20px;">
                <h3 style="margin: 0 0 12px 0; font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Audience</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                    <div style="background: #f8fafc; border-radius: 12px; padding: 12px;">
                        <p style="margin: 0; font-size: 0.7rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Vues totales</p>
                        <p style="margin: 4px 0 0 0; font-size: 1.5rem; font-weight: 800; color: #0f172a;">{{ number_format($post->total_views ?? 0, 0, ',', ' ') }}</p>
                    </div>
                    <div style="background: #f8fafc; border-radius: 12px; padding: 12px;">
                        <p style="margin: 0; font-size: 0.7rem; text-transform: uppercase; color: #94a3b8; font-weight: 600;">Vues uniques</p>
                        <p style="margin: 4px 0 0 0; font-size: 1.5rem; font-weight: 800; color: #0f172a;">{{ number_format($post->unique_views ?? 0, 0, ',', ' ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script pour scroller en haut de la modal --}}
<script>
    (function() {
        let tries = 0;
        const maxTries = 20;
        const interval = setInterval(function() {
            const modal = document.querySelector('.fi-modal');
            if (modal) {
                modal.scrollTop = 0;
                clearInterval(interval);
            }
            tries++;
            if (tries >= maxTries) clearInterval(interval);
        }, 50);
    })();
</script>
