<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action; // On utilise l'action de table ici
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => Auth::user()->can('create', Post::class)),
        ];
    }

    // Ta méthode personnalisée pour les actions de ligne
    protected function getTableActions(): array
    {
        return [
            // 👁️ PRÉVISUALISATION DANS UNE MODALE
        Action::make('preview')
    ->icon('heroicon-o-eye')
    ->color('gray') // Couleur plus sobre pour l'œil
    ->modalWidth('4xl') // On l'élargit pour que ça ressemble à un article
    ->modalContent(fn (Post $record) => view('filament.components.post-preview', ['post' => $record]))
    ->modalSubmitAction(false)
    ->modalCancelActionLabel('Fermer'),

            // 🟢 APPROUVER
            Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Post $record) => Auth::user()->can('approve', $record))
                ->action(function (Post $record) {
                    $record->approve(Auth::id());
                }),

            // 🔴 REJETER
            Action::make('reject')
                ->label('Rejeter')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Post $record) => Auth::user()->can('reject', $record))
                ->action(function (Post $record) {
                    $record->reject(Auth::id());
                }),
        ];
    }
}