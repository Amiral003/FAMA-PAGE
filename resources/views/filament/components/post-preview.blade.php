<div class="preview-container" style="background: #f8fafc; padding: 20px; font-family: 'Inter', sans-serif;">
    <div class="main-card" style="background: white; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.06); border: 1px solid #f1f5f9; overflow: hidden; max-width: 850px; margin: auto;">
        
        {{-- Header : Tag & Date --}}
        <div style="padding: 40px 40px 20px 40px;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <span style="background: {{ $post->pdf_path ? '#fee2e2' : '#dcfce7' }}; color: {{ $post->pdf_path ? '#dc2626' : '#14B82C' }}; padding: 6px 14px; border-radius: 50px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px;">
                    {{ $post->pdf_path ? 'DOCUMENT OFFICIEL' : 'COMMUNIQUÉ' }}
                </span>
                <span style="color: #64748b; font-size: 13px; display: flex; align-items: center; gap: 5px;">
                    <svg style="width: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $post->created_at->diffForHumans() }}
                </span>
            </div>
            
            <h1 style="font-size: 2.2rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin: 0; letter-spacing: -0.02em;">
                {{ $post->title }}
            </h1>
        </div>

        {{-- Zone Media : Inspirée de ton Instagram-Carousel --}}
        @php
            $allMedia = collect();
            if($post->thumbnail) $allMedia->push($post->thumbnail);
            if($post->media) {
                foreach($post->media as $m) $allMedia->push($m->file_path);
            }
        @endphp

        @if($allMedia->count() > 0)
            <div style="background: #f8fafc; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; position: relative;">
                {{-- Badge compteur --}}
                @if($allMedia->count() > 1)
                    <div style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.6); color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; z-index: 10;">
                        1 / {{ $allMedia->count() }} photos
                    </div>
                @endif

                {{-- Image principale --}}
                <div style="height: 500px; display: flex; align-items: center; justify-content: center; background: #fdfdfd;">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($allMedia->first()) }}" 
                         style="max-width: 100%; max-height: 100%; object-fit: contain;" 
                         alt="FAMa Post">
                </div>

                {{-- Miniatures --}}
                @if($allMedia->count() > 1)
                    <div style="display: flex; gap: 8px; padding: 15px; overflow-x: auto; background: white;">
                        @foreach($allMedia as $img)
                            <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; border: 2px solid {{ $loop->first ? '#14B82C' : '#f1f5f9' }}; flex-shrink: 0;">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img) }}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div style="padding: 40px;">
            {{-- Corps du texte enrichi --}}
            <div class="rich-content-preview" style="font-size: 1.2rem; line-height: 1.8; color: #334155; font-family: 'Inter', sans-serif;">
                {!! $post->content !!}
            </div>

            <style>
                .rich-content-preview strong, .rich-content-preview b { font-weight: 800; color: #0f172a; }
                .rich-content-preview ul { list-style-type: disc; margin-left: 20px; margin-bottom: 20px; }
                .rich-content-preview ol { list-style-type: decimal; margin-left: 20px; margin-bottom: 20px; }
                .rich-content-preview p { margin-bottom: 15px; }
                .rich-content-preview blockquote { border-left: 4px solid #14B82C; padding-left: 15px; font-style: italic; color: #64748b; margin: 20px 0; }
            </style>

            {{-- Signature --}}
            <div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #f1f5f9; text-align: right;">
                <div style="width: 60px; height: 4px; background: #14B82C; margin-left: auto; margin-bottom: 15px; border-radius: 2px;"></div>
                <p style="font-size: 1.3rem; font-weight: 900; color: #1e293b; text-transform: uppercase; margin: 0;">
                    {{ $post->user?->name ?? 'ADMINISTRATEUR' }}
                </p>
                <p style="font-size: 0.9rem; color: #64748b; margin: 5px 0 0 0;">
                    Direction de l'Information et des Relations Publiques des Armées
                </p>
            </div>
            {{-- Signature --}}
<div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #f1f5f9; text-align: right;">
{{-- Validateur --}}
@if($post->validator)
    <div style="
        margin-top: 25px;
        background: #f0fdf4;
        border: 1px solid #dcfce7;
        padding: 15px 20px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">
        <div style="display:flex; align-items:center; gap:10px;">
            <svg style="width:18px; color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>

            <span style="font-size:0.9rem; color:#166534; font-weight:600;">
                Validé par
            </span>
        </div>

        <span style="font-weight:800; color:#14532d;">
            {{ $post->validator->name }}
        </span>
    </div>
@endif
            {{-- Bloc PDF --}}
            @if($post->pdf_path)
                <div style="margin-top: 40px; background: #f8fafc; border: 1px solid #edf2f7; border-radius: 16px; padding: 20px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="background: #fee2e2; padding: 10px; border-radius: 10px;">
                            <svg style="width: 24px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p style="margin: 0; font-weight: 700; color: #1e293b; font-size: 0.95rem;">Communiqué Officiel</p>
                            <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Format PDF disponible</p>
                        </div>
                    </div>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->pdf_path) }}" 
                       target="_blank" 
                       style="background: #14B82C; color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.9rem;">
                         Consulter le document
                    </a>
                </div>
            @endif
        </div>

        {{-- nombre de vue  --}}

        <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
    <h3 class="text-sm font-semibold text-gray-700">Audience</h3>

    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
        <div class="rounded-md bg-gray-50 p-3">
            <p class="text-xs uppercase tracking-wide text-gray-500">Vues totales</p>
            <p class="text-lg font-bold text-gray-900">
                {{ number_format($post->total_views ?? 0, 0, ',', ' ') }}
            </p>
        </div>

        <div class="rounded-md bg-gray-50 p-3">
            <p class="text-xs uppercase tracking-wide text-gray-500">Vues uniques</p>
            <p class="text-lg font-bold text-gray-900">
                {{ number_format($post->unique_views ?? 0, 0, ',', ' ') }}
            </p>
        </div>
    </div>
</div>
    </div>
</div>