<?php

namespace App\Filament\Resources\Staff;

use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Models\Staff;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationLabel = 'Structures institutionnelles';
    protected static ?string $modelLabel = 'Structure';
    protected static ?string $pluralModelLabel = 'Structures institutionnelles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identification de la structure')
                ->description('Ministère, état-major, direction, service ou structure spécialisée.')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('initials')
                            ->label('Sigle / Initiales')
                            ->placeholder('Ex : EMGA, EMAT, DTTIA')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('order')
                            ->label('Ordre d’affichage')
                            ->numeric()
                            ->nullable()
                            ->placeholder('Ex : 1')
                            ->helperText('Plus le chiffre est petit, plus la structure apparaît en haut. Laissez vide si non défini.'),
                    ]),

                    TextInput::make('slug')
                        ->label('Lien URL')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->unique(ignoreRecord: true),

                    Grid::make(2)->schema([
                        FileUpload::make('logo')
                            ->label('Logo / Emblème')
                            ->image()
                            ->disk('public')
                            ->directory('staff-logos')
                            ->imageEditor()
                            ->maxSize(2048),

                        TextInput::make('motto')
                            ->label('Devise')
                            ->placeholder("Ex : S'instruire pour mieux servir")
                            ->maxLength(255),
                    ]),
                ]),

            Section::make('Commandement / Responsable')
                ->description('Informations sur le responsable actuel de la structure.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('leader_rank')
                            ->label('Grade / Fonction')
                            ->maxLength(255),

                        TextInput::make('leader_name')
                            ->label('Nom complet')
                            ->maxLength(255),
                    ]),

                    FileUpload::make('leader_photo')
                        ->label('Photo du responsable')
                        ->image()
                        ->disk('public')
                        ->directory('leaders')
                        ->imageEditor()
                        ->maxSize(2048),

                    Textarea::make('leader_word')
                        ->label('Mot du responsable')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),

            Section::make('Présentation, détails et missions')
                ->description('Mise en forme simple autorisée : gras, italique, listes et liens.')
                ->schema([
                    RichEditor::make('description')
                        ->label('Description générale')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'bulletList',
                            'orderedList',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),

                    RichEditor::make('missions')
                        ->label('Missions et attributions')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'bulletList',
                            'orderedList',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),
                ]),

            Section::make('Contact')
                ->description('Coordonnées publiques de la structure.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('contact_email')
                            ->label('Email de contact')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('contact_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('contact_hotline')
                            ->label('Numéro vert')
                            ->maxLength(255),

                        TextInput::make('contact_hours')
                            ->label('Heures d’ouverture')
                            ->maxLength(255),
                    ]),

                    TextInput::make('contact_address')
                        ->label('Adresse')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('contact_map_url')
                        ->label('Lien carte')
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('contact_socials')
                        ->label('Réseaux sociaux')
                        ->helperText('Format JSON : {"facebook":"https://...","x":"https://...","youtube":"https://..."}')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('order')
                    ->label('Ordre')
                    ->sortable()
                    ->badge()
                    ->placeholder('—'),

                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Structure')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('initials')
                    ->label('Sigle')
                    ->badge()
                    ->searchable(),

                TextColumn::make('leader_name')
                    ->label('Responsable')
                    ->placeholder('Non renseigné')
                    ->description(fn (Staff $record): string => $record->leader_rank ?? ''),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                // \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaff::route('/'),
            'create' => CreateStaff::route('/create'),
            'edit' => EditStaff::route('/{record}/edit'),
        ];
    }
}