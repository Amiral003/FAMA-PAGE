<?php

namespace App\Filament\Resources\Staff;

use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Models\Staff;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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
use Filament\Actions\EditAction;

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

            Section::make('Identification')
                ->description('Informations principales de la structure.')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Nom complet')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        TextInput::make('initials')
                            ->label('Sigle / Initiales')
                            ->placeholder('Ex : PR, MDAC, EMGA, DTTIA')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('order')
                            ->label('Ordre d’affichage')
                            ->numeric()
                            ->nullable()
                            ->placeholder('Ex : 1'),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('parent_staff_id')
                            ->label('Structure parente')
                            ->options(function (?Staff $record) {
                                return Staff::query()
                                    ->whereIn('initials', ['PR', 'MDAC', 'EMGA', 'MSPC'])
                                    ->when($record, fn ($query) => $query->whereKeyNot($record->id))
                                    ->orderByRaw("
                                        CASE initials
                                            WHEN 'PR' THEN 1
                                            WHEN 'MDAC' THEN 2
                                            WHEN 'EMGA' THEN 3
                                            WHEN 'MSPC' THEN 4
                                            ELSE 5
                                        END
                                    ")
                                    ->get()
                                    ->mapWithKeys(fn (Staff $staff) => [
                                        $staff->id => $staff->initials . ' — ' . $staff->name,
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Aucune structure parente')
                            ->helperText('Choisir uniquement entre PR, MDAC, EMGA ou MSPC.'),

                        TextInput::make('slug')
                            ->label('Lien URL')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('motto')
                            ->label('Devise')
                            ->placeholder("Ex : S'instruire pour mieux servir")
                            ->maxLength(255),

                        FileUpload::make('logo')
                            ->label('Logo / Emblème')
                            ->image()
                            ->disk('public')
                            ->directory('staff-logos')
                            ->imageEditor()
                            ->maxSize(2048),
                    ]),
                ]),

            Section::make('Présentation et missions')
                ->description('Description officielle et attributions de la structure.')
                ->columnSpanFull()
                ->schema([

                    RichEditor::make('missions')
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
                ]),

            Grid::make(2)
                ->columnSpanFull()
                ->schema([

                    Section::make('Chef principal')
                        ->description('Responsable principal de la structure.')
                        ->schema([
                            TextInput::make('leader_rank')
                                ->label('Grade / Fonction')
                                ->maxLength(255),

                            TextInput::make('leader_name')
                                ->label('Nom complet')
                                ->maxLength(255),

                            FileUpload::make('leader_photos')
                                ->label('Photos du chef principal')
                                ->image()
                                ->multiple()
                                ->maxFiles(3)
                                ->reorderable()
                                ->disk('public')
                                ->directory('leaders')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->helperText("Ajoutez jusqu'a 3 photos. La premiere reste utilisee comme photo principale.")
                                ->afterStateHydrated(function ($component, $state, ?Staff $record) {
                                    if (empty($state) && $record?->leader_photo) {
                                        $component->state([$record->leader_photo]);
                                    }
                                }),

                            Textarea::make('leader_word')
                                ->label('Mot du chef principal')
                                ->rows(5),
                        ]),

                    Section::make('Second chef / Chef adjoint')
                        ->description('Optionnel : remplir seulement si la structure a un second responsable.')
                        ->schema([
                            TextInput::make('second_leader_rank')
                                ->label('Grade / Fonction')
                                ->maxLength(255),

                            TextInput::make('second_leader_name')
                                ->label('Nom complet')
                                ->maxLength(255),

                            FileUpload::make('second_leader_photo')
                                ->label('Photo du second chef')
                                ->image()
                                ->disk('public')
                                ->directory('leaders')
                                ->imageEditor()
                                ->maxSize(2048),

                            Textarea::make('second_leader_word')
                                ->label('Mot du second chef')
                                ->rows(5),
                        ]),
                ]),

            Section::make('Contact public')
                ->description('Coordonnées visibles sur le site public.')
                ->columnSpanFull()
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

                TextColumn::make('parent.initials')
                    ->label('Parent')
                    ->placeholder('Aucun')
                    ->badge()
                    ->sortable(),

                TextColumn::make('leader_name')
                    ->label('Chef principal')
                    ->placeholder('Non renseigné')
                    ->description(fn (Staff $record): string => $record->leader_rank ?? ''),

                TextColumn::make('second_leader_name')
                    ->label('Second chef')
                    ->placeholder('Optionnel')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
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
