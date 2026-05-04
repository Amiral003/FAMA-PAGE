<?php

namespace App\Filament\Resources\SecurityLockouts;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SecurityLockouts\Pages\CreateSecurityLockout;
use App\Filament\Resources\SecurityLockouts\Pages\EditSecurityLockout;
use App\Filament\Resources\SecurityLockouts\Pages\ListSecurityLockouts;
use App\Filament\Resources\SecurityLockouts\Schemas\SecurityLockoutForm;
use App\Filament\Resources\SecurityLockouts\Tables\SecurityLockoutsTable;
use App\Models\SecurityLockout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SecurityLockoutResource extends Resource
{
    protected static ?string $model = SecurityLockout::class;

protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static ?string $recordTitleAttribute = 'email';

    protected static ?string $navigationLabel = 'Blocages sécurité';
protected static ?string $modelLabel = 'Blocage sécurité';
protected static ?string $pluralModelLabel = 'Blocages sécurité';

    public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('super-admin');
}

public static function getNavigationBadge(): ?string
{
    $count = SecurityLockout::query()
        ->where('locked_until', '>', now())
        ->count();

    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): string|array|null
{
    return 'danger';
}

    public static function form(Schema $schema): Schema
    {
        return SecurityLockoutForm::configure($schema);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
{
    return $table
        ->columns([
            \Filament\Tables\Columns\TextColumn::make('email')
                ->searchable()
                ->label('Email'),

            \Filament\Tables\Columns\TextColumn::make('ip_address')
                ->label('IP'),

            \Filament\Tables\Columns\TextColumn::make('severity')
                ->badge()
                ->colors([
                    'success' => 'info',
                    'warning' => 'warning',
                    'danger' => 'danger',
                    'critical' => 'danger',
                ]),

            \Filament\Tables\Columns\TextColumn::make('locked_until')
                ->label('Bloqué jusqu’à')
                ->dateTime(),

            \Filament\Tables\Columns\TextColumn::make('created_at')
                ->label('Créé le')
                ->dateTime(),
        ])
        ->filters([
            \Filament\Tables\Filters\Filter::make('active')
                ->label('Blocages actifs')
                ->query(fn ($query) => $query->where('locked_until', '>', now())),
        ])
        ->actions([
            \Filament\Actions\Action::make('unlock')
                ->label('Débloquer')
                ->color('success')
                ->icon('heroicon-o-lock-open')
                ->requiresConfirmation()
                ->action(fn ($record) => $record->delete()),
        ])
        ->defaultSort('created_at', 'desc');
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurityLockouts::route('/'),
            
        ];
    }

    public static function canCreate(): bool
{
    return false;
}

public static function canEdit($record): bool
{
    return false;
}

public static function canDelete($record): bool
{
    return auth()->user()?->hasRole('super-admin');
}
}
