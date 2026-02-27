<?php

namespace App\Filament\Resources\Staff;

use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Models\Staff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Str;

// Importations alignées sur ton PostResource
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;
    protected static ?string $navigationLabel = 'États-Majors';
    protected static ?string $modelLabel = 'État-Major';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            
            // 1. Identité de l'unité
            Section::make('Identité de l’État-Major')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        
                        TextInput::make('initials')
                            ->label('Initiales (ex: EMAT)')
                            ->required(),
                    ]),

                    TextInput::make('slug')
                        ->label('Lien URL (Slug)')
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Grid::make(2)->schema([
                        FileUpload::make('logo')
                            ->label('Logo / Emblème')
                            ->image()
                            ->disk('public')
                            ->directory('staff-logos'),
                        
                        TextInput::make('motto')
                            ->label('Devise (Ex: S\'instruire pour mieux servir)'),
                    ]),
                ]),

            // 2. Commandement (Le Chef)
            Section::make('Commandement (Chef d’État-Major)')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('leader_rank')
                            ->label('Grade du Chef'),
                        
                        TextInput::make('leader_name')
                            ->label('Nom complet du Chef'),
                    ]),

                    FileUpload::make('leader_photo')
                        ->label('Photo du Chef')
                        ->image()
                        ->disk('public')
                        ->directory('leaders'),

                    // Utilisation de Textarea ou RichEditor selon tes besoins
                    Textarea::make('leader_word')
                        ->label('Mot du Chef')
                        ->rows(5),
                ]),

            // 3. Détails et Missions
            Section::make('Détails et Missions')
                ->schema([
                    Textarea::make('description')
                        ->label('Description générale')
                        ->rows(5),
                    
                    Textarea::make('missions')
                        ->label('Missions et attributions')
                        ->rows(5),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('initials')
                    ->label('Initiales')
                    ->badge(),

                TextColumn::make('leader_name')
                    ->label('Chef actuel')
                    ->description(fn (Staff $record): string => $record->leader_rank ?? ''),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Ajoute des filtres ici si nécessaire
            ])
            ->actions([
                // \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
               
            ]);
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