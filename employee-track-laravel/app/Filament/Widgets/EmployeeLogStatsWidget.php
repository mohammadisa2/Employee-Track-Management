<?php

namespace App\Filament\Widgets;

use App\Models\EmployeeLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeLogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $user = Auth::user();
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();
        
        $query = EmployeeLog::query()
            ->byEmployee($user->id)
            ->byDateRange($startDate, $endDate);

        $totalLogs = $query->count();
        $websiteVisits = $query->clone()->websiteVisits()->count();
        $keystrokeLogs = $query->clone()->keystrokes()->count();
        $activityLogs = $query->clone()->activities()->count();
        $uniqueDomains = $query->clone()->websiteVisits()
            ->distinct('domain')
            ->count('domain');

        // Get yesterday's data for comparison
        $yesterdayQuery = EmployeeLog::query()
            ->byEmployee($user->id)
            ->byDateRange(now()->subDay()->startOfDay(), now()->subDay()->endOfDay());
        $yesterdayTotal = $yesterdayQuery->count();
        
       $change = $yesterdayTotal > 0 ? (($totalLogs - $yesterdayTotal) / $yesterdayTotal) * 100 : 0;
        
        if ($change > 0) {
            $changeDescription = '↗️ +' . number_format($change, 1) . '% dari kemarin';
        } elseif ($change < 0) {
            $changeDescription = '↘️ ' . number_format($change, 1) . '% dari kemarin';
        } else {
            $changeDescription = '➡️ Sama dengan kemarin';
        }
        
        $changeColor = $change > 0 ? 'success' : ($change < 0 ? 'danger' : 'gray');

        return [
            Stat::make('📊 Total Aktivitas Hari Ini', number_format($totalLogs))
                ->description($changeDescription)
                ->descriptionIcon($change > 0 ? 'heroicon-m-arrow-trending-up' : ($change < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($changeColor)
                ->chart(array_fill(0, 7, rand(10, 100))),
                
            Stat::make('🌐 Kunjungan Website', number_format($websiteVisits))
                ->description('Website yang dikunjungi hari ini')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('success')
                ->chart(array_fill(0, 7, rand(5, 50))),
                
            Stat::make('⌨️ Aktivitas Keyboard', number_format($keystrokeLogs))
                ->description('Log keystroke yang tercatat')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('warning')
                ->chart(array_fill(0, 7, rand(20, 80))),
                
            Stat::make('🎯 Log Aktivitas Umum', number_format($activityLogs))
                ->description('Aktivitas lainnya')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('info')
                ->chart(array_fill(0, 7, rand(15, 60))),
                
            Stat::make('🔗 Domain Unik', number_format($uniqueDomains))
                ->description('Website berbeda yang dikunjungi')
                ->descriptionIcon('heroicon-m-link')
                ->color('gray')
                ->chart(array_fill(0, 7, rand(1, 20))),
        ];
    }
}