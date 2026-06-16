<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MyRecentPosts extends TableWidget
{
    protected static ?string $heading = 'Mes derniers posts';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Post::query()
            ->where('user_id', Filament::auth()->id())
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
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y'),
            ])
            ->paginated(false);
    }
}
