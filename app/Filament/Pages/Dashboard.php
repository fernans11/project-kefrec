<?php

namespace App\Filament\Pages;

use App\Models\Ingredient;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        if (session()->get('low_stock_toast_shown')) {
            return;
        }

        $count = Ingredient::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        if ($count > 0) {
            Notification::make()
                ->title('Stok bahan baku menipis')
                ->body("Ada {$count} bahan baku di bawah batas minimum.")
                ->warning()
                ->send();
        }

        session()->put('low_stock_toast_shown', true);
    }
}
