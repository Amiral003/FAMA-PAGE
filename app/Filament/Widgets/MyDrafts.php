<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MyDrafts extends TableWidget
{
    protected static ?string $heading = 'Mes brouillons';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Post::query()
            ->where('user_id', Filament::auth()->id())
            ->where('status', Post::STATUS_BROUILLON)
            ->latest()
            ->limit(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(60),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label('Dernière modification')
                    ->dateTime('d/m/Y'),
            ])
            ->paginated(false);
    }
}
