<?php

namespace App\Filament\Widgets;

use App\Models\EmployeeLog;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DailyActivityChart extends ChartWidget
{
    protected static ?string $heading = '📈 Tren Aktivitas 7 Hari Terakhir';
    
    protected static string $color = 'info';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];
    
    protected static ?string $maxHeight = '300px';
    
    protected function getData(): array
    {
        $user = Auth::user();
        $data = [];
        $websiteData = [];
        $keystrokeData = [];
        $activityData = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            
            $totalCount = EmployeeLog::query()
                ->byEmployee($user->id)
                ->whereDate('logged_at', $date)
                ->count();
                
            $websiteCount = EmployeeLog::query()
                ->byEmployee($user->id)
                ->websiteVisits()
                ->whereDate('logged_at', $date)
                ->count();
                
            $keystrokeCount = EmployeeLog::query()
                ->byEmployee($user->id)
                ->keystrokes()
                ->whereDate('logged_at', $date)
                ->count();
                
            $activityCount = EmployeeLog::query()
                ->byEmployee($user->id)
                ->activities()
                ->whereDate('logged_at', $date)
                ->count();
                
            $data[] = $totalCount;
            $websiteData[] = $websiteCount;
            $keystrokeData[] = $keystrokeCount;
            $activityData[] = $activityCount;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Total Logs',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Website Visits',
                    'data' => $websiteData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Keystrokes',
                    'data' => $keystrokeData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Activities',
                    'data' => $activityData,
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}