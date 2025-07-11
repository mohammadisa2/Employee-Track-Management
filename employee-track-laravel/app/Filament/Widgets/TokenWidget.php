<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TokenWidget extends Widget
{
    protected static string $view = 'filament.widgets.token-widget';
    
    protected function getViewData(): array
    {
        return [
            'token' => session('sanctum_token'),
        ];
    }
}