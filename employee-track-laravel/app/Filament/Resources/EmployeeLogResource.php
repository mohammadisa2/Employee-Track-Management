<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeLogResource\Pages;
use App\Models\EmployeeLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EmployeeLogResource extends Resource
{
    protected static ?string $model = EmployeeLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Employee Logs';
    
    protected static ?string $modelLabel = 'Employee Log';
    
    protected static ?string $pluralModelLabel = 'Employee Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'website_visit' => 'Website Visit',
                        'keystroke' => 'Keystroke',
                        'activity' => 'Activity',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('url')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255),
                Forms\Components\TextInput::make('domain')
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('activity_data')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('form_data')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('logged_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'website_visit',
                        'warning' => 'keystroke',
                        'success' => 'activity',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('logged_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'website_visit' => 'Website Visit',
                        'keystroke' => 'Keystroke',
                        'activity' => 'Activity',
                    ]),
                SelectFilter::make('employee_id')
                    ->options(function () {
                        return EmployeeLog::distinct('employee_id')
                            ->pluck('employee_id', 'employee_id')
                            ->toArray();
                    })
                    ->searchable(),
                Filter::make('logged_at')
                    ->form([
                        Forms\Components\DatePicker::make('logged_from'),
                        Forms\Components\DatePicker::make('logged_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['logged_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('logged_at', '>=', $date),
                            )
                            ->when(
                                $data['logged_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('logged_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('logged_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeLogs::route('/'),
            'create' => Pages\CreateEmployeeLog::route('/create'),
            'view' => Pages\ViewEmployeeLog::route('/{record}'),
            'edit' => Pages\EditEmployeeLog::route('/{record}/edit'),
        ];
    }
}