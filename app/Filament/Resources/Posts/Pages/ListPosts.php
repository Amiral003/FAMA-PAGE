<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

use App\Models\Post;


class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => Auth::user()->can('create', \App\Models\Post::class)),
        ];
    }

    protected function getTableActions(): array
    {
        return [

            // 🔵 SOUMETTRE
            // Action::make('submit')
            //     ->label('Soumettre')
            //     ->icon('heroicon-o-paper-airplane')
            //     ->color('warning')
            //     ->requiresConfirmation()
            //     ->visible(fn ($record) => Auth::user()->can('submit', $record))
            //      ->action(function (Post $record){
            //         $record->submit(Auth::id());
            //     }),

            // 🟢 APPROUVER
            Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => Auth::user()->can('approve', $record))
                ->action(function (Post $record){
                    $record->approve(Auth::id());
                }),

            // 🔴 REJETER
            Action::make('reject')
                ->label('Rejeter')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn ($record) => Auth::user()->can('reject', $record))
               ->action(function (Post $record){
                    $record->reject(Auth::id());
                }),
        ];
    }
}