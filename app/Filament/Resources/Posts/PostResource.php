<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

// ✅ Filament Form Components
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;

// ✅ Filament Layout
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

// ✅ Filament Table
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationLabel = 'Publications';
    protected static ?string $modelLabel = 'Communiqué';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            /**
             * ⚠️ Section "Correction demandée"
             * Affichée uniquement si status=revision et notes présentes.
             */
            Section::make('⚠️ Correction demandée')
                ->description('.')
                ->aside()
                ->visible(fn (?Post $record) =>
                    $record !== null
                    && $record->status === Post::STATUS_REVISION
                    && !empty($record->rejection_notes)
                )
                ->schema([
                    Placeholder::make('rejection_notes_display')
                        ->label('Motif du rejet :')
                        ->content(fn (?Post $record) => $record?->rejection_notes)
                        ->extraAttributes(['class' => 'text-danger-600 font-bold']),
                ])
                ->columnSpanFull(),

            /**
             * ✅ TYPE (POST NORMAL UNIQUEMENT)
             * On retire "flash" car désormais il se crée via le bouton "Créer flash".
             */
            Grid::make(2)->schema([
                Select::make('type')
                    ->label('Type de publication')
                    ->options([
                        Post::TYPE_ARTICLE => 'Actualité / Article',
                        Post::TYPE_PDF     => 'Recrutement / Pdf',
                        Post::TYPE_VIDEO => 'Vidéothèque / Vidéo',
                    ])
                    ->required()
                    ->live() // important pour les champs conditionnels
                    ->native(false)
                    ->afterStateUpdated(function ($state, callable $set) {
        // Quand tu changes de type, on nettoie ce qui ne sert plus
        if ($state !== Post::TYPE_VIDEO) {
            $set('video_url', null);
            $set('video_platform', null);
            $set('video_thumbnail_url', null);
        }
        if ($state === Post::TYPE_PDF) {
            $set('content', null); // optionnel: si tu veux forcer content null pour PDF
        }
    }),
            ]),

            /**
             * ✅ TITRE + SLUG
             */
            TextInput::make('title')
                ->label('Titre du post')
                ->required()
                ->live(onBlur: true)
                ->columnSpanFull()
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

            TextInput::make('slug')
                ->label('Lien URL (Slug)')
                ->disabled()
                ->dehydrated()
                ->required()
                ->columnSpanFull(),

            /**
             * ✅ Miniature (PDF uniquement)
             */
            FileUpload::make('thumbnail')
                ->label("Image de couverture du document (Miniature)")
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->image()
                ->disk('public')
                ->directory('thumbnails')
                ->imageEditor()
                ->maxSize(10240)
                
                ->columnSpanFull()
                ->visible(fn ($get) => $get('type') === Post::TYPE_PDF)
                ->required(fn ($get) => $get('type') === Post::TYPE_PDF),

            /**
             * ✅ CONTENU / DESCRIPTION
             * - Requis pour article
             * - Optionnel (nullable) pour PDF (comme tu veux)
             */
            RichEditor::make('content')
                ->label('Corps du communiqué / Description')
                ->placeholder('Rédigez ici le contenu détaillé...')
                ->required(fn ($get) => $get('type') === Post::TYPE_ARTICLE)
                ->columnSpanFull()
                ->visible(fn ($get) => $get('type') !== Post::TYPE_VIDEO)
                ->toolbarButtons([
                    'bold',
'italic',
'underline',
'bulletList',
'orderedList',
'link',
'h2',
'h3',
'undo',
'redo',
                    
                ]),

                Section::make('Vidéo')
    ->schema([
        TextInput::make('video_url')
            ->label('Lien vidéo')
            ->placeholder('https://youtube.com/watch?v=... ou https://facebook.com/... ou https://...mp4')
            ->url()
            ->maxLength(2048)
            ->required(fn ($get) => $get('type') === Post::TYPE_VIDEO)
            ->live(onBlur: true)
            ->afterStateUpdated(function ($state, callable $set) {
                if (! $state) {
                    $set('video_platform', null);
                    $set('video_thumbnail_url', null);
                    return;
                }

                $url = trim($state);

                $platform = 'other';
                if (Str::contains($url, ['youtube.com', 'youtu.be'])) $platform = 'youtube';
                elseif (Str::contains($url, ['facebook.com', 'fb.watch'])) $platform = 'facebook';
                elseif (Str::endsWith(Str::lower(parse_url($url, PHP_URL_PATH) ?? ''), '.mp4')) $platform = 'mp4';

                $set('video_platform', $platform);

                if ($platform === 'youtube') {
                    $id = null;
                    if (Str::contains($url, 'youtu.be/')) {
                        $id = Str::before(Str::after($url, 'youtu.be/'), '?');
                    }
                    if (! $id && Str::contains($url, 'watch?v=')) {
                        $id = Str::before(Str::after($url, 'watch?v='), '&');
                    }
                    if ($id) {
                        $set('video_thumbnail_url', "https://img.youtube.com/vi/{$id}/hqdefault.jpg");
                    }
                }
            }),

        TextInput::make('video_platform')
            ->label('Plateforme')
            ->disabled()
            ->dehydrated(),

        TextInput::make('video_thumbnail_url')
            ->label('Miniature (optionnel)')
            ->url()
            ->maxLength(2048)
            ->nullable(),
    ])
    ->visible(fn ($get) => $get('type') === Post::TYPE_VIDEO)
    ->collapsed(),

            /**
             * ✅ PDF (PDF uniquement)
             */
            FileUpload::make('pdf_path')
    ->label('Fichier PDF Officiel')
    ->disk('public')
    ->directory('documents')
    ->acceptedFileTypes(['application/pdf'])
    ->maxSize(51200) // 50 Mo
    ->helperText('PDF uniquement. Taille recommandée : moins de 20 Mo. Taille maximale : 50 Mo. Pour les scans, utilisez noir et blanc ou niveaux de gris, 300 DPI maximum, puis compressez le PDF avec PDF24 ou Adobe Acrobat avant l’envoi.')
    ->visible(fn ($get) => $get('type') === Post::TYPE_PDF)
    ->required(fn ($get) => $get('type') === Post::TYPE_PDF)
    ->columnSpanFull(),

            /**
             * ✅ Galerie médias (article uniquement)
             * PDF => caché
             */
Repeater::make('media')
    ->label('Médias additionnels (Images)')
    ->relationship('media')
    ->schema([
        FileUpload::make('file_path')
            ->label('Photo')
            ->acceptedFileTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->image()
            ->imageEditor()
            ->maxSize(30720)
            ->disk('public')
            ->directory('posts')
            ->required(),

        TextInput::make('caption')
            ->label('Légende de la photo')
            ->placeholder('Ex : Le Chef d’État-Major lors de la cérémonie...')
            ->maxLength(255)
            ->columnSpanFull(),
    ])
    ->collapsible()
    ->reorderable('order')
    ->grid(2)
    ->columnSpanFull()
    ->hidden(fn ($get) => in_array($get('type'), [Post::TYPE_PDF, Post::TYPE_VIDEO], true)),

            /**
             * ✅ Auteur automatique
             */
            Hidden::make('user_id')
                ->default(fn (?Post $record) => $record?->user_id ?? Filament::auth()->id())
                ->dehydrated(),
        ]);
    }

    /**
     * ✅ Eager loading global (admin)
     */
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()
        ->with(['validator', 'user', 'media']);

    $user = Filament::auth()->user();

    if ($user?->hasRole('redacteur')) {
        $query->where('user_id', $user->id);
    }

    return $query;
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('rejection_notes')
                    ->label('Notes du validateur')
                    ->searchable()
                    ->wrap()
                    ->color('danger')
                    ->visible(fn ($record) => $record?->status === Post::STATUS_REVISION),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Post::STATUS_BROUILLON => 'Brouillon',
                        Post::STATUS_REVISION  => 'En révision',
                        Post::STATUS_PUBLIE    => 'Publié',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Post::STATUS_BROUILLON => 'gray',
                        Post::STATUS_REVISION  => 'warning',
                        Post::STATUS_PUBLIE    => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        Post::STATUS_BROUILLON => 'heroicon-m-pencil-square',
                        Post::STATUS_REVISION  => 'heroicon-m-eye',
                        Post::STATUS_PUBLIE    => 'heroicon-m-check-badge',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->sortable(),
                TextColumn::make('scheduled_at')
    ->label('Publication prévue')
    ->dateTime('d/m/Y H:i')
    ->sortable()
    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filtrer par statut')
                    ->multiple()
                    ->options([
                        Post::STATUS_BROUILLON => 'Brouillon',
                        Post::STATUS_REVISION  => 'En révision',
                        Post::STATUS_PUBLIE    => 'Publié',
                       
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit'   => EditPost::route('/{record}/edit'),
        ];
    }
}