<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action; 
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    // Actions en haut de la page (Bouton Créer)
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel État-Major')
                ->icon('heroicon-m-plus'),
        ];
    }

    // Actions sur chaque ligne (Voir, Modifier, Supprimer)
    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->iconButton()
                ->color('gray')
                ->modalContent(fn (Staff $record) => view('filament.components.staff-preview', ['staff' => $record]))
                ->modalSubmitAction(false),

            EditAction::make()
                ->iconButton(),

            DeleteAction::make()
                ->iconButton(),
        ];
    }

    // Actions de groupe (Sélection multiple)
    protected function getTableBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->label('Supprimer la sélection'),
                
                // Exemple : Une action groupée personnalisée pour activer plusieurs staffs d'un coup
                \Filament\Actions\BulkAction::make('activate')
                    ->label('Activer la sélection')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_active' => true]))
                    ->deselectRecordsAfterCompletion()
                    ->color('success'),
            ]),
        ];
    }
}
