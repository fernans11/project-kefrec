<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan staff dummy versi lama agar laporan konsisten dengan dua akun karyawan demo.
        Staff::query()->whereIn('name', [
            'Andi Pratama', 'Siti Aulia', 'Budi Santoso', 'Maya Putri',
            'Rizky Hidayat', 'Nina Amelia', 'Fajar Ramadhan', 'Lina Marlina',
        ])->delete();

        $staffMembers = [
            ['name' => 'Siti Kasir', 'position' => 'Kasir', 'phone' => '081290000003', 'is_active' => true],
            ['name' => 'Budi Dapur', 'position' => 'Dapur', 'phone' => '081290000004', 'is_active' => true],
        ];

        foreach ($staffMembers as $data) {
            Staff::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $staffIds = Staff::query()->whereIn('name', ['Siti Kasir', 'Budi Dapur'])->pluck('id')->all();
        if (empty($staffIds)) {
            return;
        }

        $start = Carbon::now()->subDays(6)->startOfDay();
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $start->copy()->addDays($i)->toDateString();
        }

        foreach ($staffIds as $staffId) {
            foreach ($dates as $date) {
                Attendance::updateOrCreate(
                    ['staff_id' => $staffId, 'date' => $date],
                    $this->fakeAttendanceData($date)
                );
            }
        }
    }

    private function fakeAttendanceData(string $date): array
    {
        $statuses = ['hadir', 'hadir', 'hadir', 'izin', 'sakit', 'alpha'];
        $status = $statuses[array_rand($statuses)];

        $clockIn = null;
        $clockOut = null;

        if ($status === 'hadir') {
            $clockIn = Carbon::parse($date . ' 08:00')->addMinutes(rand(0, 30));
            $clockOut = Carbon::parse($date . ' 16:00')->addMinutes(rand(0, 30));
        }

        return [
            'status' => $status,
            'clock_in' => $clockIn?->format('H:i:s'),
            'clock_out' => $clockOut?->format('H:i:s'),
            'notes' => $status === 'hadir' ? null : 'Catatan otomatis',
        ];
    }
}
