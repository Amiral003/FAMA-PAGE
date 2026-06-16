<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Top 5 des publications les plus vues
        </x-slot>

        <x-slot name="description">
            Vue rapide des contenus les plus consultés.
        </x-slot>

        <style>
            .top-viewed-posts-scroll {
                overflow-x: auto;
                padding-bottom: 6px;
            }

            .top-viewed-posts-grid {
                display: grid;
                grid-template-columns: repeat(5, minmax(220px, 1fr));
                gap: 12px;
                min-width: 1120px;
            }

            @media (max-width: 1180px) {
                .top-viewed-posts-scroll {
                    overflow-x: visible;
                }

                .top-viewed-posts-grid {
                    grid-template-columns: repeat(2, minmax(220px, 1fr));
                    min-width: 0;
                }
            }

            @media (max-width: 640px) {
                .top-viewed-posts-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="top-viewed-posts-scroll">
            <div class="top-viewed-posts-grid">
                @forelse ($posts as $post)
                    @php
                        $image = null;

                        if ($post->type === 'video' && ! empty($post->video_thumbnail_url)) {
                            $image = $post->video_thumbnail_url;
                        } elseif (! empty($post->thumbnail)) {
                            $image = \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail);
                        } elseif ($post->media && $post->media->count() > 0) {
                            $image = \Illuminate\Support\Facades\Storage::disk('public')->url($post->media->first()->file_path);
                        }

                        $rank = $loop->iteration;

                        $typeLabel = match ($post->type) {
                            'flash' => 'Flash',
                            'video' => 'Vidéo',
                            'pdf' => 'PDF',
                            'recrutement' => 'Recrutement',
                            default => 'Communiqué',
                        };

                        $typeClasses = match ($post->type) {
                            'flash' => 'background:#fff7ed;color:#c2410c;',
                            'video' => 'background:#eff6ff;color:#1d4ed8;',
                            'pdf' => 'background:#fef2f2;color:#b91c1c;',
                            'recrutement' => 'background:#f5f3ff;color:#6d28d9;',
                            default => 'background:#ecfdf5;color:#15803d;',
                        };

                        $rankClasses = match ($rank) {
                            1 => 'background:#fef3c7;color:#92400e;',
                            2 => 'background:#e5e7eb;color:#374151;',
                            3 => 'background:#ffedd5;color:#9a3412;',
                            default => 'background:#f1f5f9;color:#334155;',
                        };
                    @endphp

                    <div style="
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 16px;
                        overflow: hidden;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
                        min-height: 100%;
                    ">
                        <div style="position: relative; height: 110px; background: #f1f5f9;">
                            @if ($image)
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $post->title }}"
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                >
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2 1.586-1.586a2 2 0 012.828 0L20 14m-6-8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif

                            <div style="position:absolute; top:8px; left:8px;">
                                <span style="
                                    {{ $rankClasses }}
                                    display:inline-flex;
                                    align-items:center;
                                    justify-content:center;
                                    width:28px;
                                    height:28px;
                                    border-radius:999px;
                                    font-size:12px;
                                    font-weight:800;
                                ">
                                    {{ $rank }}
                                </span>
                            </div>

                            <div style="position:absolute; top:8px; right:8px;">
                                <span style="
                                    {{ $typeClasses }}
                                    padding:4px 8px;
                                    border-radius:999px;
                                    font-size:10px;
                                    font-weight:800;
                                ">
                                    {{ $typeLabel }}
                                </span>
                            </div>
                        </div>

                        <div style="padding: 12px;">
                            <p style="
                                margin: 0 0 8px 0;
                                font-size: 13px;
                                line-height: 1.35;
                                font-weight: 800;
                                color: #111827;
                                min-height: 36px;
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                            ">
                                {{ $post->title }}
                            </p>

                            <p style="margin: 0 0 10px 0; font-size: 11px; color: #6b7280;">
                                {{ optional($post->published_at)->format('d/m/Y') ?? '—' }}
                            </p>

                            <div style="display: flex; gap: 8px;">
                                <div style="
                                    flex:1;
                                    background:#f8fafc;
                                    border-radius:10px;
                                    padding:8px;
                                    text-align:center;
                                ">
                                    <p style="margin:0; font-size:10px; color:#64748b; font-weight:700; text-transform:uppercase;">
                                        Total
                                    </p>
                                    <p style="margin:4px 0 0 0; font-size:14px; font-weight:900; color:#0f172a;">
                                        {{ number_format($post->total_views ?? 0, 0, ',', ' ') }}
                                    </p>
                                </div>

                                <div style="
                                    flex:1;
                                    background:#f8fafc;
                                    border-radius:10px;
                                    padding:8px;
                                    text-align:center;
                                ">
                                    <p style="margin:0; font-size:10px; color:#64748b; font-weight:700; text-transform:uppercase;">
                                        Uniques
                                    </p>
                                    <p style="margin:4px 0 0 0; font-size:14px; font-weight:900; color:#0f172a;">
                                        {{ number_format($post->unique_views ?? 0, 0, ',', ' ') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="
                        grid-column: 1 / -1;
                        border: 1px dashed #cbd5e1;
                        border-radius: 16px;
                        background: white;
                        padding: 24px;
                        text-align: center;
                        color: #64748b;
                        font-size: 14px;
                    ">
                        Aucune donnée d'audience disponible pour le moment.
                    </div>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
