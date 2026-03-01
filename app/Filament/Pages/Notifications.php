<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class Notifications extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Notifications';
    protected static ?int $navigationSort = 5;

    // ✅ IMPORTANT: chez toi, pas de `protected static $view`
    public function getView(): string
    {
        return 'filament.pages.notifications';
    }

    public function table(Table $table): Table
    {
        return $table
            // ✅ Table::query veut un Builder, pas une Relation
            ->query(fn () => Auth::user()->notifications()->orderByRaw('read_at is null desc')->latest()->getQuery())
            
            ->recordAction('open')
            // ✅ Action globale = header action (stable)
            ->headerActions([
                Action::make('markAllRead')
                    ->label('Tout marquer comme lu')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(fn () => Auth::user()->unreadNotifications->markAsRead()),
            ])

            ->columns([
                Tables\Columns\IconColumn::make('read_at')
    ->label('')
    ->boolean()
    ->trueIcon('heroicon-o-check')
    ->falseIcon('heroicon-o-bell-alert'),
    Tables\Columns\TextColumn::make('data.title')
        ->label('Notification')
        ->weight('bold')
        ->wrap()
        ->description(fn (DatabaseNotification $record) => $record->data['body'] ?? null)
        ->color('danger'),

    Tables\Columns\TextColumn::make('data.post_title')
        ->label('Publication')
        ->wrap()
        ->color('gray'),

    Tables\Columns\TextColumn::make('data.validator_name')
        ->label('Validateur')
        ->badge(),

    Tables\Columns\TextColumn::make('created_at')
        ->label('Date')
        ->since() // ex: "2 minutes ago"
        ->sortable(),
])
->actions([
    Action::make('open')
        ->label('') // pas besoin
        ->action(function (DatabaseNotification $record) {
            if (is_null($record->read_at)) {
                $record->markAsRead();
            }

            $url = $record->data['url'] ?? null;

            if ($url) {
                redirect()->to($url);
            }
        }),
])

            // ->actions([
            //     Action::make('open')
            //         ->label('Ouvrir')
            //         ->icon('heroicon-o-arrow-top-right-on-square')
            //         ->action(function (DatabaseNotification $record) {
            //             if (is_null($record->read_at)) {
            //                 $record->markAsRead();
            //             }

            //             $url = $record->data['url'] ?? null;

            //             if ($url) {
            //                 redirect()->to($url);
            //             }
            //         }),

            //     Action::make('markRead')
            //         ->label('Marquer lue')
            //         ->icon('heroicon-o-check')
            //         ->visible(fn (DatabaseNotification $record) => is_null($record->read_at))
            //         ->action(fn (DatabaseNotification $record) => $record->markAsRead()),
            // ])
            ;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) (Auth::user()?->unreadNotifications()->count() ?? 0);
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}