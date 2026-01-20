<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceSelfController extends Controller
{
    public function index()
    {
        $staff = Staff::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('attendance.self', [
            'staff' => $staff,
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
        ]);

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::query()
            ->where('staff_id', $data['staff_id'])
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return back()->with('error', 'Anda sudah melakukan check-in hari ini.');
        }

        if (! $attendance) {
            $attendance = new Attendance([
                'staff_id' => $data['staff_id'],
                'date' => $today,
            ]);
        }

        $attendance->status = 'hadir';
        $attendance->clock_in = now()->format('H:i:s');
        $attendance->clock_out = null;
        $attendance->save();

        return back()->with('success', 'Check-in berhasil.');
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
        ]);

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::query()
            ->where('staff_id', $data['staff_id'])
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || ! $attendance->clock_in) {
            return back()->with('error', 'Belum ada check-in untuk hari ini.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah melakukan check-out hari ini.');
        }

        $attendance->clock_out = now()->format('H:i:s');
        $attendance->save();

        return back()->with('success', 'Check-out berhasil.');
    }
}
