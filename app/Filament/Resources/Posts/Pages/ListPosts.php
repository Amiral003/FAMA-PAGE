<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Notifications\PostRejectedNotification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    /**
     * ✅ Header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Créer post')
                ->visible(fn () => Auth::user()->can('create', Post::class)),

            Action::make('createFlash')
                ->label('Créer flash')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->visible(fn () => Auth::check())
                ->modalHeading('Créer un Flash Info (publication immédiate)')
                ->modalSubmitActionLabel('Publier')
                ->form([
                    Textarea::make('content')
                        ->label('Texte du flash')
                        ->placeholder("Écrivez un flash (taille d’un paragraphe)...")
                        ->rows(4)
                        ->required()
                        ->maxLength(600),

                    FileUpload::make('thumbnail')
                        ->label("Image du flash")
                        ->image()
                        ->disk('public')
                        ->directory('flashes')
                        ->imageEditor()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $userId = Auth::id();
                    $flashText = trim($data['content']);

                    Post::create([
                        'title'           => Str::limit($flashText, 120, '...'),
                        'slug'            => Str::slug(Str::limit($flashText, 80, '')),
                        'type'            => Post::TYPE_FLASH,
                        'status'          => Post::STATUS_PUBLIE,
                        'thumbnail'       => $data['thumbnail'],
                        'content'         => $flashText,
                        'user_id'         => $userId,
                        'validated_by'    => $userId,
                        'validated_at'    => now(),
                        'published_at'    => now(),
                        'rejection_notes' => null,
                    ]);

                    Notification::make()
                        ->title('Flash publié immédiatement ⚡')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * ✅ Actions table
     */
    protected function getTableActions(): array
    {
        return [
            // 👁️ PREVIEW
            Action::make('preview')
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->iconButton()
                ->color('gray')
                ->modalWidth('4xl')
                ->modalContent(fn (Post $record) => view('filament.components.post-preview', ['post' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer')
                ->modalFooterActions([]),

            // ✏️ EDIT
            EditAction::make()->iconButton(),

            // ✅ CORRIGÉ (rédacteur) : revision -> brouillon
            Action::make('markFixed')
                ->label('Corrigé')
                ->icon('heroicon-o-check')
                ->iconButton()
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (Post $record) => Auth::user()->can('markFixed', $record))
                ->action(function (Post $record) {
                    $record->markFixed(Auth::id());

                    Notification::make()
                        ->title('Post remis en brouillon (prêt à être revalidé)')
                        ->success()
                        ->send();
                }),

            // 🟢 PUBLIER (validateur) - visible seulement pour brouillon
            Action::make('approve')
                ->label('Publier')
                ->icon('heroicon-o-check-circle')
                ->iconButton()
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Post $record) =>
                    Auth::user()->can('approve', $record) &&
                    $record->status === Post::STATUS_BROUILLON
                )
                ->action(function (Post $record) {
                    $record->publish(Auth::id());

                    Notification::make()
                        ->title('Post publié')
                        ->success()
                        ->send();
                }),

            // 🕒 PROGRAMMER
            Action::make('schedulePublication')
                ->label('Programmer')
                ->icon('heroicon-o-clock')
                ->iconButton()
                ->color('info')
                ->visible(fn (Post $record) =>
                    Auth::user()->can('approve', $record) &&
                    in_array($record->status, [Post::STATUS_BROUILLON, Post::STATUS_PROGRAMME])
                )
                ->form([
                    DateTimePicker::make('scheduled_at')
                        ->label('Date et heure de publication')
                        ->required()
                        ->native(true)
                        ->timezone('Africa/Bamako')
                        ->minDate(now()->startOfMinute())
                        ->default(now()->addMinutes(10))
                        ->helperText('Cliquez sur le champ ou saisissez directement les chiffres au clavier.'),
                ])
                ->action(function (Post $record, array $data) {
                    switch ($record->status) {
                        case Post::STATUS_PUBLIE:
                            Notification::make()
                                ->title('❌ Impossible de reprogrammer')
                                ->body('Ce post est déjà publié. Vous ne pouvez pas reprogrammer un contenu déjà publié.')
                                ->danger()
                                ->send();
                            return;

                        case Post::STATUS_REVISION:
                            Notification::make()
                                ->title('⚠️ Action impossible')
                                ->body('Ce post a été renvoyé pour révision. Veuillez le modifier avant de pouvoir le programmer.')
                                ->warning()
                                ->send();
                            return;

                        case Post::STATUS_BROUILLON:
                        case Post::STATUS_PROGRAMME:
                            try {
                                $record->schedulePublication(
                                    Auth::id(),
                                    $data['scheduled_at']
                                );

                                $formattedDate = \Carbon\Carbon::parse($data['scheduled_at'])->format('d/m/Y à H:i');

                                Notification::make()
                                    ->title('✅ Publication programmée avec succès')
                                    ->body("Le post sera publié le {$formattedDate}")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('❌ Erreur lors de la programmation')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                            return;

                        default:
                            Notification::make()
                                ->title('⚠️ Action non autorisée')
                                ->body('Seuls les brouillons peuvent être programmés.')
                                ->warning()
                                ->send();
                            return;
                    }
                })
                ->modalHeading('Programmer la publication')
                ->modalWidth('md')
                ->modalSubmitActionLabel('Valider la programmation'),

            // 🔴 REJETER (validateur)
            Action::make('reject')
                ->label('Renvoyer')
                ->icon('heroicon-o-arrow-path')
                ->iconButton()
                ->color('danger')
                ->visible(fn (Post $record) =>
                    Auth::user()->can('reject', $record) &&
                    in_array($record->status, [Post::STATUS_BROUILLON, Post::STATUS_PUBLIE])
                )
                ->form([
                    Textarea::make('rejection_notes')
                        ->label('Motif du rejet')
                        ->placeholder('Précisez les corrections à apporter...')
                        ->required(),
                ])
                ->action(function (Post $record, array $data) {
                    try {
                        $record->rejectForRevision(
                            Auth::id(),
                            $data['rejection_notes']
                        );

                        if ($record->author) {
                            $record->author->notify(
                                new PostRejectedNotification(
                                    $record,
                                    $data['rejection_notes'],
                                    Auth::user()
                                )
                            );
                        }

                        Notification::make()
                            ->title('Post renvoyé pour correction')
                            ->body('L\'auteur a été notifié.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('❌ Erreur lors du renvoi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->successNotification(null)
                ->modalHeading('Renvoyer pour correction')
                ->modalSubmitActionLabel('Envoyer en révision'),
        ];
    }

    /**
     * ✅ Filtrage de la table
     */
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $filter = request()->get('status_filter');

        return match ($filter) {
            'a_traiter'  => $query->where('status', Post::STATUS_BROUILLON),
            'revisions'  => $query->where('status', Post::STATUS_REVISION),
            'publies'    => $query->where('status', Post::STATUS_PUBLIE),
            'programmes' => $query->where('status', Post::STATUS_PROGRAMME),
            default      => $query,
        };
    }
}
