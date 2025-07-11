<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TokenWidget extends Widget
{
    protected static string $view = 'filament.widgets.token-widget';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];
    
    protected function getViewData(): array
    {
        return [
            'token' => session('sanctum_token'),
        ];
    }
}