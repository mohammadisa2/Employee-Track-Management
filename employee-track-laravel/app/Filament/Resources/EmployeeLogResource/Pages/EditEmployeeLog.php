<?php

namespace App\Filament\Resources\EmployeeLogResource\Pages;

use App\Filament\Resources\EmployeeLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeLog extends EditRecord
{
    protected static string $resource = EmployeeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}