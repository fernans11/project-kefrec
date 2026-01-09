<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MemberController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        [$totalSpent, $points, $status] = $this->computeMemberStats($user->id);

        return view('customer.member', [
            'user' => $user,
            'totalSpent' => $totalSpent,
            'points' => $points,
            'status' => $status,
        ]);
    }

    /**
     * Aman untuk berbagai struktur DB: jika tabel/kolom belum ada -> fallback 0.
     * Nanti jika struktur transaksi Anda sudah final, kita rapikan jadi akurat 100%.
     */
    private function computeMemberStats(int $userId): array
    {
        $totalSpent = 0;

        // Coba ambil dari tabel transactions jika ada (karena route orders Anda pakai {transaction})
        if (Schema::hasTable('transactions')) {
            $q = DB::table('transactions')->where('user_id', $userId);

            // jika punya kolom status, ambil yang sukses/paid/done saja
            if (Schema::hasColumn('transactions', 'status')) {
                $q->whereIn('status', ['paid', 'success', 'done', 'completed', 'selesai']);
            }

            // total kolom umum: total, total_amount, grand_total, amount
            foreach (['total', 'total_amount', 'grand_total', 'amount'] as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $totalSpent = (int) $q->sum($col);
                    break;
                }
            }
        }

        // Rules poin (bisa Anda ubah): 1 poin / 10.000
        $points = (int) floor($totalSpent / 10000);

        // Status member (bisa Anda ubah)
        $status = match (true) {
            $points >= 500 => 'Platinum',
            $points >= 200 => 'Gold',
            $points >= 50  => 'Silver',
            default        => 'Bronze',
        };

        return [$totalSpent, $points, $status];
    }
}
