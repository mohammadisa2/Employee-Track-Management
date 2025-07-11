<?php

namespace App\Filament\Resources\EmployeeLogResource\Pages;

use App\Filament\Resources\EmployeeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmployeeLogs extends ListRecords
{
    protected static string $resource = EmployeeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Logs'),
            'website_visits' => Tab::make('Website Visits')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'website_visit')),
            'keystrokes' => Tab::make('Keystrokes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'keystroke')),
            'activities' => Tab::make('Activities')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'activity')),
        ];
    }
}