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

// Importations
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
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
            // 1. On commence par le Type et le Statut (Ligne du haut)
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

            // 2. Le Titre allongé sur toute la largeur (Full Width)
            TextInput::make('title')
                ->label('Titre du post')
                ->placeholder('Entrez le titre complet ici...')
                ->required()
                ->live(onBlur: true)
                ->columnSpanFull() // Étire le champ sur toute la ligne
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

            // 3. Le Slug juste en dessous, un peu plus discret
            TextInput::make('slug')
                ->label('Lien URL (Slug)')
                ->disabled()
                ->dehydrated()
                ->required()
                ->columnSpanFull(),

            // 4. Image de couverture (Toujours visible pour la cohérence du front-end)
            FileUpload::make('thumbnail')
                ->label("Image de couverture principale")
                ->image()
                ->disk('public')
                ->directory('thumbnails')
                ->imageEditor()
                ->required() // Recommandé pour le design du site
                ->columnSpanFull(),

            // 5. Contenu texte
            Textarea::make('content')
                ->label('Corps du communiqué / Description')
                ->rows(8)
                ->required()
                ->columnSpanFull(),

            // 6. Section PDF (Conditionnelle)
            FileUpload::make('pdf_path')
                ->label('Fichier PDF Officiel')
                ->disk('public')
                ->directory('documents')
                ->acceptedFileTypes(['application/pdf'])
                ->visible(fn ($get) => $get('type') === 'pdf')
                ->required(fn ($get) => $get('type') === 'pdf')
                ->columnSpanFull(),

            // 7. Galerie photos (Masquée si c'est uniquement un PDF)
            Repeater::make('media')
                ->label('Galerie photos additionnelles')
                ->relationship('media')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Image')
                        ->image()
                        ->disk('public')
                        ->directory('posts')
                        ->required(),
                ])
                ->collapsible()
                ->grid(3) // Organise les miniatures en grille de 3
                ->columnSpanFull()
                ->hidden(fn ($get) => $get('type') === 'pdf'),

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

                // ImageColumn::make('thumbnail')
                //     ->label('Aperçu')
                //     ->circular(),

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
