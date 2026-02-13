<div class="page-background p-2" style="background: #f8fafc; font-family: sans-serif;">
    <div class="content-card" style="background: white; border-radius: 16px; border: 1px solid #f1f5f9; overflow: hidden;">
        
        {{-- Header & Meta --}}
        <div class="p-6">
            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                {{-- <span style="background: {{ $post->pdf_path ? '#ef4444' : '#14B82C' }}; color: white; padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                    {{ $post->pdf_path ? 'DOCUMENT OFFICIEL' : 'COMMUNIQUÉ' }}
                </span> --}}
                <span style="color: #64748b; font-size: 13px;">
                    <i class="heroicon-o-clock" style="width: 14px; display: inline-block;"></i>
                    {{ $post->created_at->diffForHumans() }}
                </span>
            </div>
            
            <h1 style="font-size: 1.8rem; font-weight: 900; color: #0f172a; line-height: 1.2; margin: 0; letter-spacing: -0.5px;">
                {{ $post->title }}
            </h1>
        </div>

        {{-- Image Principale --}}
        @if($post->thumbnail)
            <div style="width: 100%; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->thumbnail) }}" 
                     style="width: 100%; max-height: 400px; object-fit: cover; display: block;" 
                     alt="Main Image">
            </div>
        @endif

        <div class="p-6">
            {{-- Corps du texte --}}
            <div style="font-size: 1.1rem; line-height: 1.7; color: #334155; white-space: pre-wrap; margin-bottom: 30px;">
                {!! nl2br(e($post->content)) !!}
            </div>

            {{-- Galerie (Inspiré de ta grille Vue) --}}
            @if($post->media && count($post->media) > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 30px 0;">
                    @foreach($post->media as $item)
                        <div style="border-radius: 8px; overflow: hidden; height: 120px; border: 1px solid #e2e8f0;">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->file_path) }}" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Signature FAMa --}}
            <div style="border-top: 2px solid #f1f5f9; padding-top: 20px; margin-top: 40px; text-align: right;">
                <div style="width: 40px; height: 3px; background: #14B82C; margin-left: auto; margin-bottom: 10px;"></div>
                <p style="font-size: 1.1rem; font-weight: 900; color: #1e293b; text-transform: uppercase; margin: 0;">
                    {{ $post->user?->name ?? 'ADMINISTRATEUR' }}
                </p>
                {{-- <p style="font-size: 0.8rem; color: #64748b; margin: 0;">
                    Direction de l'Information et des Relations Publiques des Armées
                </p> --}}
            </div>

            {{-- Section PDF --}}
            @if($post->pdf_path)
                <div style="margin-top: 30px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid #edf2f7; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px; color: #475569; font-weight: 600;">
                        <span style="color: #ef4444; font-size: 20px;">PDF</span>
                        <span style="font-size: 14px;">Communiqué officiel</span>
                    </div>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->pdf_path) }}" 
                       target="_blank" 
                       style="background: #14B82C; color: white; padding: 6px 15px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 13px;">
                        Télécharger
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>