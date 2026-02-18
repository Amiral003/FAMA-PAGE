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

// --- IMPORTATIONS CORRIGÉES ---
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section; // Section spécifique aux formulaires
use Filament\Schemas\Components\Grid;    // Grid spécifique aux formulaires
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

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

            // ⚠️ Section de correction (N'apparaît que si nécessaire)
            Section::make('⚠️ Correction demandée')
                ->description('.')
                ->aside()
                // On s'assure que la section est visible si le statut est 'revision'
                ->visible(fn (?Post $record) => $record !== null && $record->status === 'revision' && !empty($record->rejection_notes))
                ->schema([
                    // On ajoute un Placeholder pour afficher le texte de rejet en gras/rouge
                    \Filament\Forms\Components\Placeholder::make('rejection_notes_display')
                        ->label('Motif du rejet :')
                        ->content(fn (?Post $record) => $record?->rejection_notes)
                        ->extraAttributes(['class' => 'text-danger-600 font-bold']),
                ])
                ->columnSpanFull(),

            // 1. Type de publication
            Grid::make(2)->schema([
                Select::make('type')
                    ->label('Type de publication')
                    ->options([
                        'flash' => 'Flash Info / Communiqué',
                        'article' => 'Actualité / Article',
                        'pdf' => 'Document Officiel / PDF',
                    ])
                    ->required()
                    ->live()
                    ->native(false),
            ]),

            // 2. Titre et Slug
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

            // 3. Image de couverture (Conditionnelle au PDF)
            FileUpload::make('thumbnail')
                ->label("Image de couverture du document (Miniature)")
                ->image()
                ->disk('public')
                ->directory('thumbnails')
                ->imageEditor()
                ->columnSpanFull()
                ->visible(fn ($get) => $get('type') === 'pdf') 
                ->required(fn ($get) => $get('type') === 'pdf'),

            // 4. Contenu
            Textarea::make('content')
                ->label('Corps du communiqué / Description')
                ->rows(8)
                ->required()
                ->columnSpanFull(),

            // 5. Fichier PDF
            FileUpload::make('pdf_path')
                ->label('Fichier PDF Officiel')
                ->disk('public')
                ->directory('documents')
                ->acceptedFileTypes(['application/pdf'])
                ->visible(fn ($get) => $get('type') === 'pdf')
                ->required(fn ($get) => $get('type') === 'pdf')
                ->columnSpanFull(),

            // 6. Galerie Médias
            Repeater::make('media')
                ->label('Médias additionnels (Images ou Vidéos)')
                ->relationship('media')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Fichier')
                        ->acceptedFileTypes(['image/*', 'video/*']) 
                        ->disk('public')
                        ->directory('posts')
                        ->required(),
                ])
                ->collapsible()
                ->grid(3)
                ->columnSpanFull()
                ->hidden(fn ($get) => $get('type') === 'pdf'),

            // 7. Auteur (Automatique)
            Hidden::make('user_id')
                ->default(fn () => Filament::auth()->id())
                ->dehydrated(),
        ]);
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
                    ->visible(fn ($record) => $record?->status === 'revision'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'brouillon' => 'Brouillon',
                        'revision' => 'En révision',
                        'publie' => 'Publié',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'brouillon' => 'gray',
                        'revision' => 'warning',
                        'publie' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'brouillon' => 'heroicon-m-pencil-square',
                        'revision' => 'heroicon-m-eye',
                        'publie' => 'heroicon-m-check-badge',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filtrer par statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'revision' => 'En révision',
                        'publie' => 'Publié',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}