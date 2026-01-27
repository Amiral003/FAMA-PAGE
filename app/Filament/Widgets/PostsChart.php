<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PostsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Publications validées (30 derniers jours)';
    }

    protected function getData(): array
    {
        // 1. On génère les dates des 30 derniers jours
        $days = collect(range(0, 29))
            ->map(fn ($i) => now()->subDays($i)->format('Y-m-d'))
            ->reverse();

        // 2. On récupère les données groupées par jour
        $counts = Post::where('status', 'publie')
            ->whereDate('validated_at', '>=', now()->subDays(29))
            ->select(DB::raw("DATE(validated_at) as date"), DB::raw('count(*) as aggregate'))
            ->groupBy(DB::raw("DATE(validated_at)"))
            ->pluck('aggregate', 'date');

        // 3. On remplit les jours vides avec des zéros
        $data = $days->map(function ($date) use ($counts) {
            return $counts->get($date, 0);
        });

        return [
            'datasets' => [
                [
                    'label' => 'Articles validés',
                    'data' => $data->values()->toArray(),
                    'fill' => 'start',
                    'borderColor' => '#14a44d', 
                    'backgroundColor' => 'rgba(20, 164, 77, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->map(fn ($date) => Carbon::parse($date)->format('d/m'))->values()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Changé en PUBLIC pour corriger l'erreur FatalError
     */
    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}