<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;
use App\Filament\Widgets\MonthlySalesWidget;
use App\Filament\Widgets\MonthlyCategoriesWidget;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\AccountWidget::class,
            Widgets\FilamentInfoWidget::class,
        ];
    }
    public function getWidgets(): array
    {
        return [
            MonthlySalesWidget::class,
            MonthlyCategoriesWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 12,
            'md' => 12,
            'lg' => 12,
        ];
    }
}