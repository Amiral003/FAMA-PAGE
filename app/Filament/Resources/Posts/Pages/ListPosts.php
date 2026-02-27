<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action; 
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\PostRejectedNotification;

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

    protected function getTableActions(): array
    {
        return [
            // 👁️ PRÉVISUALISATION
            Action::make('preview')
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->iconButton()
                ->color('gray')
                ->modalWidth('4xl')
                ->modalContent(fn (Post $record) => view('filament.components.post-preview', ['post' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),

            // ✏️ MODIFIER
            EditAction::make()
                ->iconButton(),

            // 🟢 APPROUVER
            Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->iconButton()
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Post $record) => Auth::user()->can('approve', $record))
                ->action(function (Post $record) {
                    $record->approve(Auth::id());
                    
                }),

            // 🔴 REJETER
            Action::make('reject')
    ->label('Rejeter pour révision')
    ->icon('heroicon-o-arrow-path')
    ->iconButton()
    ->color('danger')
    ->visible(fn (Post $record) => Auth::user()->can('reject', $record))

    ->form([
        Textarea::make('rejection_notes')
            ->label('Motif du rejet')
            ->placeholder("Précisez les corrections à apporter...")
            ->required(),
    ])

    ->action(function (Post $record, array $data) {

        $record->update([
            'status' => 'revision',
            'rejection_notes' => $data['rejection_notes'],
            'validator_id' => Auth::id(),
        ]);

        // ✅ NOUVEAU : notification envoyée au rédacteur
        if ($record->user) {
            $record->user->notify(
                new PostRejectedNotification(
                    $record,
                    $data['rejection_notes']
                )
            );
        }

        // 🔊 déclenche le son + toast Filament
        $this->dispatchBrowserEvent('notification-received');

        // notification de succès (inchangée)
        \Filament\Notifications\Notification::make()
            ->title('Post renvoyé pour correction')
            ->danger()
            ->send();
    })

    ->modalHeading('Renvoyer pour correction')
    ->modalSubmitActionLabel('Envoyer en révision'),
    
        ];
    }

protected function getTableQuery(): Builder
{
    $query = parent::getTableQuery();

    $filter = request()->get('status_filter');

    return match ($filter) {
        'a_valider' => $query->whereIn('status', ['brouillon', 'revision']),
        'publies'   => $query->where('status', 'publie'),
        default     => $query,
    };
}

    
}