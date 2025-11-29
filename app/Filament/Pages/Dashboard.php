<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MonitoringStats;
use App\Filament\Widgets\RecentActivities;
use App\Filament\Widgets\SystemNotifications;
use App\Filament\Widgets\UserActivityChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // Label di sidebar (kalau menu sidebar aktif)
    protected static ?string $navigationLabel = 'Monitoring';

    // Judul halaman di bagian atas
    protected static ?string $title = 'Panel Admin - Monitoring';

    // Icon di menu (opsional, bisa diganti)
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    /**
     * Atur widget apa saja yang muncul di halaman dashboard admin.
     * HARUS public karena di parent class juga public.
     */
    public function getWidgets(): array
    {
        return [
            MonitoringStats::class,     // kartu-kartu ringkasan
            RecentActivities::class,    // tabel "Aktivitas Terkini"
            SystemNotifications::class, // notifikasi sistem
            UserActivityChart::class,   // grafik aktivitas user
        ];
    }

    /**
     * (Opsional) Atur jumlah kolom grid dashboard.
     * Ini bisa kamu sesuaikan lagi nanti supaya mirip layout Figma.
     */
    public function getColumns(): int | string | array
    {
        // contoh: 2 kolom di layar medium, 4 kolom di layar besar
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }
}
