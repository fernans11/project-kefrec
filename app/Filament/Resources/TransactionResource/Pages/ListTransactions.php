<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderWidgets(): array
    {
        return TransactionResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_filtered')
                ->label('Export Semua (Filter)')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $records = $this->getFilteredTableQuery()
                        ->with(['customer', 'cashier', 'items.product'])
                        ->get();

                    return $this->downloadZip($records);
                }),
            Actions\CreateAction::make()->label('Transaksi Baru'),
        ];
    }

    private function downloadZip(Collection $records)
    {
        $timestamp = now()->format('Ymd_His');
        $filename = "transactions_filtered_{$timestamp}.zip";

        $tmpFile = tempnam(sys_get_temp_dir(), 'kefrec_csv_');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $summary = fopen('php://temp', 'w+');
        fputcsv($summary, [
            'Invoice',
            'Tanggal',
            'Customer',
            'Kasir',
            'Status',
            'Metode Pembayaran',
            'Subtotal',
            'Diskon',
            'Pajak',
            'Total',
            'Bayar',
            'Kembalian',
        ]);
        foreach ($records as $record) {
            fputcsv($summary, [
                $record->invoice_no,
                $record->created_at?->format('Y-m-d H:i:s'),
                $record->customer?->name ?? '-',
                $record->cashier?->name ?? '-',
                match ($record->status) {
                    'draft' => 'Draf',
                    'pending_cashier' => 'Menunggu Kasir',
                    'paid' => 'Dibayar',
                    'processing' => 'Diproses',
                    'ready' => 'Siap Diambil',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                    default => $record->status,
                },
                match ($record->payment_method) {
                    'cash' => 'Tunai',
                    'qris' => 'QRIS',
                    'transfer' => 'Transfer',
                    default => $record->payment_method ?? '-',
                },
                $record->subtotal,
                $record->discount,
                $record->tax,
                $record->total,
                $record->paid_amount,
                $record->change_amount,
            ]);
        }
        rewind($summary);
        $zip->addFromString('transactions_summary.csv', stream_get_contents($summary));
        fclose($summary);

        $items = fopen('php://temp', 'w+');
        fputcsv($items, [
            'Invoice',
            'Tanggal',
            'Customer',
            'Status',
            'Metode Pembayaran',
            'Kategori',
            'Produk',
            'Qty',
            'Harga',
            'Subtotal Item',
        ]);
        foreach ($records as $record) {
            foreach ($record->items as $item) {
                fputcsv($items, [
                    $record->invoice_no,
                    $record->created_at?->format('Y-m-d H:i:s'),
                    $record->customer?->name ?? '-',
                    match ($record->status) {
                        'draft' => 'Draf',
                        'pending_cashier' => 'Menunggu Kasir',
                        'paid' => 'Dibayar',
                        'processing' => 'Diproses',
                        'ready' => 'Siap Diambil',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $record->status,
                    },
                    match ($record->payment_method) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                        default => $record->payment_method ?? '-',
                    },
                    $item->product?->category ?? '-',
                    $item->product?->name ?? '-',
                    $item->qty,
                    $item->price,
                    $item->subtotal,
                ]);
            }
        }
        rewind($items);
        $zip->addFromString('transactions_items.csv', stream_get_contents($items));
        fclose($items);

        $zip->close();

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
