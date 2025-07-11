<?php

namespace App\Filament\Resources\EmployeeLogResource\Pages;

use App\Filament\Resources\EmployeeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeLog extends ViewRecord
{
    protected static string $resource = EmployeeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}