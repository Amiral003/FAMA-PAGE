<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AdminSeparator extends Widget
{
    protected static ?int $sort = 2; // Il se place après tes stats perso
    protected string $view = 'filament.widgets.admin-separator';
    
    public function getColumnSpan(): int | string | array { return 'full'; }

    public static function canView(): bool
    {
        // On l'affiche pour les admins et les validateurs
        return Auth::user()->hasAnyRole(['super-admin', 'validateur']);
    }
}