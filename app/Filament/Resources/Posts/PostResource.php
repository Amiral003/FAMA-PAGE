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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Str;
use Filament\Forms\Components\Section;


class PostResource extends Resource
{

    // Traduction du titre dans le menu de navigation
    protected static ?string $navigationLabel = 'Publications';

    // Traduction du titre au singulier (utilisé pour "Créer Communiqué")
    protected static ?string $modelLabel = 'Communiqué';

    // Traduction du titre au pluriel
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

 public static function form(Schema $schema): Schema
{
    return $schema->components([
        TextInput::make('title')
            ->label('Titre')
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

        TextInput::make('slug')
            ->disabled()
            ->dehydrated()
            ->required(),

        Select::make('type')
            ->label('Format de publication')
            ->options([
                'flash' => 'Flash Info',
                'article' => 'Article Actualité',
                'pdf' => 'Document Officiel / PDF',
            ])
            ->required()
            ->reactive(),

        FileUpload::make('thumbnail')
            ->label("Image de couverture")
            ->image()
            ->disk('public')
            ->directory('thumbnails'),

        Textarea::make('content')
            ->label('Texte / Description')
            ->rows(4)
            ->columnSpanFull(),

        FileUpload::make('pdf_path')
            ->label('Télécharger le fichier PDF')
            ->disk('public')
            ->directory('documents')
            ->acceptedFileTypes(['application/pdf'])
            ->visible(fn ($get) => $get('type') === 'pdf'),

        Repeater::make('media')
            ->label('Galerie Photos')
            ->relationship('media')
            ->schema([
                FileUpload::make('file_path')
                    ->image()
                    ->disk('public')
                    ->directory('posts')
                    ->required(),
            ])
            ->columnSpanFull(),

        Hidden::make('user_id')
            ->default(fn () => Filament::auth()->id()),
    ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        Post::STATUS_BROUILLON => 'Brouillon',
                        Post::STATUS_PUBLIE => 'Approuvée',
                        Post::STATUS_REVISION => 'En Révision',
                        default => $state,
                    })
                    ->colors([
                        'secondary' => Post::STATUS_BROUILLON,
                        'success' => Post::STATUS_PUBLIE,
                        'danger' => Post::STATUS_REVISION,
                    ]),

                \Filament\Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Couverture')
                    ->circular(),

                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Auteur')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
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