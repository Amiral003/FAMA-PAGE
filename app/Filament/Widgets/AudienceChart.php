<?php

namespace App\Filament\Widgets;

use App\Models\PostViewDaily;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AudienceChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $maxHeight = '260px';

    public ?string $filter = '30';

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['super-admin', 'validateur']) ?? false;
    }

    public function getHeading(): string
    {
        return 'Activité du site';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 jours',
            '30' => '30 jours',
            '90' => '90 jours',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = (int) $this->filter;

        $days = collect(range(0, $activeFilter - 1))
            ->map(fn ($i) => now()->subDays($i)->format('Y-m-d'))
            ->reverse();

        $counts = PostViewDaily::query()
            ->whereDate('view_date', '>=', now()->subDays($activeFilter - 1)->toDateString())
            ->select('view_date', DB::raw('SUM(hits) as total_hits'))
            ->groupBy('view_date')
            ->pluck('total_hits', 'view_date');

        return [
            'datasets' => [[
                'label' => 'Vues par jour',
                'data' => $days->map(fn ($date) => (int) ($counts[$date] ?? 0))->values()->toArray(),
                'fill' => true,
                'borderColor' => '#14a44d',
                'backgroundColor' => 'rgba(20, 164, 77, 0.10)',
                'tension' => 0.3,
            ]],
            'labels' => $days->map(fn ($date) => Carbon::parse($date)->format('d/m'))->values()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}