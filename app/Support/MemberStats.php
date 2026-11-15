<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Transaction;

class MemberStats
{
    public static function forCustomer(?Customer $customer): array
    {
        if (! $customer) {
            return self::empty();
        }

        $transactions = Transaction::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->get(['total']);

        $totalSpent = (int) $transactions->sum('total');
        $points = (int) $transactions->sum(fn (Transaction $transaction): int => self::pointsForTotal((int) $transaction->total));

        return [
            'points' => $points,
            'total_spent' => $totalSpent,
            'status' => self::statusForPoints($points),
        ];
    }

    public static function empty(): array
    {
        return [
            'points' => 0,
            'total_spent' => 0,
            'status' => 'Bronze',
        ];
    }

    public static function pointsForTotal(int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        if ($total < 100000) {
            return 1;
        }

        if ($total < 200000) {
            return 2;
        }

        return 5;
    }

    public static function statusForPoints(int $points): string
    {
        return match (true) {
            $points >= 200 => 'Honorable Member',
            $points >= 150 => 'Platinum',
            $points >= 100 => 'Gold',
            $points >= 50 => 'Silver',
            default => 'Bronze',
        };
    }
}
