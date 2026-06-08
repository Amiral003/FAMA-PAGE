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

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    /**
     * ✅ Header actions :
     * - "Créer post" (ancien bouton Create, renommé)
     * - "Créer flash" (nouveau, publication instantanée)
     */
    protected function getHeaderActions(): array
    {
        return [
            /**
             * ✅ Bouton Create renommé "Créer post"
             * Crée des posts normaux (article/pdf)
             */
            CreateAction::make()
                ->label('Créer post')
                ->visible(fn () => Auth::user()->can('create', Post::class)),

            /**
             * ⚡ Bouton => "Créer flash"
             * Formulaire ultra rapide, publication immédiate
             */
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

                    \Filament\Notifications\Notification::make()
                        ->title('Flash publié immédiatement ⚡')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * ✅ Actions table (Gestion des états, Validation et Programmation)
     */
    protected function getTableActions(): array
    {
        return [
            // 👁️ PREVIEW (lecture seule)
            Action::make('preview')
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->iconButton()
                ->color('gray')
                ->modalWidth('4xl')
                ->modalContent(fn (Post $record) => view('filament.components.post-preview', ['post' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),

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

                    \Filament\Notifications\Notification::make()
                        ->title('Post remis en brouillon (prêt à être revalidé)')
                        ->success()
                        ->send();
                }),

            // 🟢 PUBLIER (validateur) : brouillon -> publie
            Action::make('approve')
                ->label('Publier')
                ->icon('heroicon-o-check-circle')
                ->iconButton()
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Post $record) => Auth::user()->can('approve', $record))
                ->action(function (Post $record) {
                    $record->publish(Auth::id());

                    \Filament\Notifications\Notification::make()
                        ->title('Post publié')
                        ->success()
                        ->send();
                }),

            // 🕒 PROGRAMMER (Saisie libre et complète autorisée)
            Action::make('schedulePublication')
                ->label('Programmer')
                ->icon('heroicon-o-clock')
                ->iconButton()
                ->color('info')
                ->visible(fn (Post $record) => Auth::user()->can('approve', $record))
                ->form([
                    DateTimePicker::make('scheduled_at')
                        ->label('Date et heure de publication')
                        ->required()
                        ->native(true) // 🔥 On repasse en natif pour transformer le champ en un vrai <input> HTML saisissable
                        ->timezone('Africa/Bamako') // Aligné sur ton fuseau horaire
                        ->minDate(now()->startOfMinute()) // Bloque la validation si c'est dans le passé
                        ->default(now()->addMinutes(10)) // Date initiale par défaut
                        ->helperText('Cliquez sur le champ ou saisissez directement les chiffres au clavier (jour, mois, année, heure).'),
                ])
                ->action(function (Post $record, array $data) {
                    $record->schedulePublication(
                        Auth::id(),
                        $data['scheduled_at']
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Publication programmée')
                        ->body('Le post changera de statut automatiquement à la date choisie.')
                        ->success()
                        ->send();
                })
                ->modalHeading('Programmer la publication')
                ->modalWidth('md')
                ->modalSubmitActionLabel('Valider la programmation'),

            // 🔴 REJETER (validateur) : brouillon -> revision + notes + notification
            Action::make('reject')
                ->label('Renvoyer')
                ->icon('heroicon-o-arrow-path')
                ->iconButton()
                ->color('danger')
                ->visible(fn (Post $record) => Auth::user()->can('reject', $record))
                ->form([
                    Textarea::make('rejection_notes')
                        ->label('Motif du rejet')
                        ->placeholder('Précisez les corrections à apporter...')
                        ->required(),
                ])
                ->action(function (Post $record, array $data) {
                    $record->rejectForRevision(
                        Auth::id(),
                        $data['rejection_notes']
                    );

                    // Notifier l’auteur (rédacteur)
                    if ($record->author) {
                        $record->author->notify(
                            new PostRejectedNotification(
                                $record,
                                $data['rejection_notes'],
                                Auth::user()
                            )
                        );
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Post renvoyé pour correction')
                        ->danger()
                        ->send();
                })
                ->successNotification(null)
                ->modalHeading('Renvoyer pour correction')
                ->modalSubmitActionLabel('Envoyer en révision'),
        ];
    }

    /**
     * ✅ Filtrage de la table via query param (?status_filter=...)
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
