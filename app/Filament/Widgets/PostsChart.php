<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostsChart extends ChartWidget
{
    protected static ?int $sort = 4;
    protected ?string $maxHeight = '200px'; // Très compact pour éviter le scroll
    public ?string $filter = '30'; 

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super-admin', 'validateur']);
    }

    public function getHeading(): string { return 'Historique des publications validées'; }

    protected function getFilters(): ?array
    {
        return ['7' => '7 jours', '30' => '30 jours', '90' => '90 jours'];
    }

    protected function getData(): array
    {
        $activeFilter = (int) $this->filter;
        $days = collect(range(0, $activeFilter - 1))
            ->map(fn ($i) => now()->subDays($i)->format('Y-m-d'))
            ->reverse();

        $counts = Post::where('status', 'publie')
            ->whereNotNull('validated_at') 
            ->whereDate('validated_at', '>=', now()->subDays($activeFilter - 1))
            ->select(DB::raw("DATE(validated_at) as date"), DB::raw('count(*) as aggregate'))
            ->groupBy(DB::raw("DATE(validated_at)"))
            ->pluck('aggregate', 'date');

        return [
            'datasets' => [[
                'label' => 'Articles validés',
                'data' => $days->map(fn ($date) => $counts->get($date, 0))->values()->toArray(),
                'fill' => 'start',
                'borderColor' => '#14a44d', 
                'backgroundColor' => 'rgba(20, 164, 77, 0.1)',
                'tension' => 0.3,
            ]],
            'labels' => $days->map(fn ($date) => Carbon::parse($date)->format('d/m'))->values()->toArray(),
        ];
    }

    protected function getType(): string { return 'line'; }
    public function getColumnSpan(): int | string | array { return 'full'; }
}