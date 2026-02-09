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
        // Section Titre et Slug
        Grid::make(2)->schema([
            TextInput::make('title')
                ->label('Titre du post')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

            TextInput::make('slug')
                ->label('Slug (URL)')
                ->disabled()
                ->dehydrated()
                ->required(),
        ]),

        // Type et Statut
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

        // Image de couverture - MODIFICATION ICI
        FileUpload::make('thumbnail')
            ->label("Image de couverture (Aperçu)")
            ->image()
            ->disk('public')
            ->directory('thumbnails')
            ->visible(fn ($get) => $get('type') === 'pdf') // Apparaît seulement si PDF est choisi
            ->required(fn ($get) => $get('type') === 'pdf')
            ->columnSpanFull(),

        // Contenu texte
        Textarea::make('content')
            ->label('Contenu ou Description')
            ->rows(6)
            ->columnSpanFull(),

        // Le fichier PDF
        FileUpload::make('pdf_path')
            ->label('Fichier PDF Officiel')
            ->disk('public')
            ->directory('documents')
            ->acceptedFileTypes(['application/pdf'])
            ->visible(fn ($get) => $get('type') === 'pdf')
            ->required(fn ($get) => $get('type') === 'pdf')
            ->columnSpanFull(),

        // Galerie photos
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
