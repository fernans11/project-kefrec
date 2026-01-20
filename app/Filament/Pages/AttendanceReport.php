<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Laporan Absensi';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 220;
    protected static ?string $title = 'Laporan Absensi';

    protected static string $view = 'filament.pages.attendance-report';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getReportQuery())
            ->columns([
                Tables\Columns\TextColumn::make('staff_name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hadir')
                    ->label('Hadir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('izin')
                    ->label('Izin')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sakit')
                    ->label('Sakit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alpha')
                    ->label('Alpha')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('date', '<=', $date));
                    }),
            ])
            ->defaultSort('staff_name')
            ->headerActions([
                Tables\Actions\Action::make('export_csv')
                    ->label('Export CSV (Ringkasan)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $records = $this->getFilteredTableQuery()->get();
                        $timestamp = now()->format('Ymd_His');
                        $filename = "laporan_absensi_{$timestamp}.csv";

                        return response()->streamDownload(function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, [
                                'Staff',
                                'Hadir',
                                'Izin',
                                'Sakit',
                                'Alpha',
                            ]);

                            foreach ($records as $row) {
                                fputcsv($handle, [
                                    $row->staff_name,
                                    $row->hadir,
                                    $row->izin,
                                    $row->sakit,
                                    $row->alpha,
                                ]);
                            }

                            fclose($handle);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ]);
    }

    private function getReportQuery(): Builder
    {
        return Attendance::query()
            ->join('staff', 'staff.id', '=', 'attendances.staff_id')
            ->selectRaw('MIN(attendances.id) as id')
            ->selectRaw('staff.name as staff_name')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN attendances.status = 'izin' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN attendances.status = 'sakit' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN attendances.status = 'alpha' THEN 1 ELSE 0 END) as alpha")
            ->groupBy('staff.id', 'staff.name');
    }
}
