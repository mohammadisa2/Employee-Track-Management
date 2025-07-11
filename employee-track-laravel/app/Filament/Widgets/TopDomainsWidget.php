<?php

namespace App\Filament\Widgets;

use App\Models\EmployeeLog;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TopDomainsWidget extends BaseWidget
{
    protected static ?string $heading = '🏆 Top 10 Domain Hari Ini';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                EmployeeLog::query()
                    ->byEmployee(Auth::id())
                    ->websiteVisits()
                    ->byDateRange(now()->startOfDay(), now()->endOfDay())
                    ->selectRaw('domain, COUNT(*) as visits, MAX(logged_at) as last_visit, MIN(id) as record_id')
                    ->groupBy('domain')
                    ->orderBy('visits', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->label('🌐 Domain')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('visits')
                    ->label('📊 Jumlah Kunjungan')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 50 => 'danger',
                        $state >= 20 => 'warning',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('last_visit')
                    ->label('🕒 Kunjungan Terakhir')
                    ->dateTime('H:i')
                    ->sortable()
                    ->color('gray'),
            ])
            ->defaultSort('visits', 'desc')
            ->paginated(false)
            ->striped();
    }
    
    public function getTableRecordKey($record): string
    {
        return (string) $record->domain;
    }
}