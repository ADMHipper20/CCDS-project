<?php

namespace App\Filament\Widgets;

use App\Models\OrderProduct;
use App\Models\Category;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = 'Top Monthly Categories';

    protected int | string | array $columnSpan = 4;

    protected function getData(): array
    {
        $data = Category::select(
            'categories.title',
            DB::raw('COUNT(order_products.id) as total_orders')
        )
        ->join('products', 'products.id', '=', 'categories.id')
        ->join('order_products', 'order_products.product_id', '=', 'products.id')
        ->where('order_products.created_at', '>=', now()->subDays(30))
        ->groupBy('categories.title')
        ->orderByDesc('total_orders')
        ->get();

        $labels = $data->pluck('title')->toArray();
        $orders = $data->pluck('total_orders')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $orders,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}