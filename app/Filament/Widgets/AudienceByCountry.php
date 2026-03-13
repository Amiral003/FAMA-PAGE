<?php

namespace App\Filament\Widgets;

use App\Models\PostViewDaily;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class AudienceByCountry extends Widget
{
    protected string $view = 'filament.widgets.audience-by-country';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $countries = PostViewDaily::query()
            ->select('country', DB::raw('COUNT(*) as visitors'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('visitors')
            ->limit(10)
            ->get();

        return [
            'countries' => $countries,
        ];
    }
}