<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlySalesWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales Chart';

    protected int | string | array $columnSpan = 8;

    protected function getData(): array
    {
        $data = Order::select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total) as total_revenue')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('day')
        ->orderBy('day')
        ->get();

        $labels = $data->pluck('day')->map(fn($day) => date('d M', strtotime($day)))->toArray();
        $orders = $data->pluck('total_orders')->toArray();
        $revenue = $data->pluck('total_revenue')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Orders',
                    'data' => $orders,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.3)',
                    'yAxisID' => 'y',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Total Revenue (Rp)',
                    'data' => $revenue,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.3)',
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
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
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Total Orders',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (Rp)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                'position' => 'bottom',
                ],
            ],
        ];
    }
}