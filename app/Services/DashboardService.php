<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get summary statistics for the dashboard.
     *
     * @return array<string, mixed>
     */
    public function getSummaryStats(): array
    {
        return [
            'total_visits' => Transaction::where('status', Transaction::STATUS_PAID)->count(),
            'total_revenue' => Transaction::where('status', Transaction::STATUS_PAID)->sum('grand_total'),
            'active_vouchers' => $this->getActiveVouchersCount(),
            'voucher_usage' => Transaction::whereNotNull('total_discount')
                ->where('total_discount', '>', 0)
                ->count(),
        ];
    }

    /**
     * Get top insurances by visit count.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopInsurancesByVisits(int $limit = 5): Collection
    {
        return Transaction::where('status', Transaction::STATUS_PAID)
            ->select('insurance_name', DB::raw('count(*) as total'))
            ->groupBy('insurance_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top insurances by revenue generated.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopInsurancesByRevenue(int $limit = 5): Collection
    {
        return Transaction::where('status', Transaction::STATUS_PAID)
            ->select('insurance_name', DB::raw('sum(grand_total) as total'))
            ->groupBy('insurance_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Get monthly visit trend for the last N months.
     *
     * @param int $months
     * @return Collection
     */
    public function getMonthlyVisitTrend(int $months = 6): Collection
    {
        return Transaction::where('status', Transaction::STATUS_PAID)
            ->where('paid_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw("to_char(paid_at, 'YYYY-MM') as month"),
                DB::raw('count(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Helper to get active vouchers count.
     *
     * @return int
     */
    private function getActiveVouchersCount(): int
    {
        return Voucher::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })->count();
    }
}
