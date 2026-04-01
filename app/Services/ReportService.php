<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service class for handling Reporting business logic.
 * Following PSR-12 standards and Service Design Pattern.
 */
class ReportService
{
    /**
     * Get transactions from yesterday.
     *
     * @return Collection
     */
    public function getDailyReport(): Collection
    {
        return Transaction::whereDate('paid_at', Carbon::yesterday())
            ->where('status', Transaction::STATUS_PAID)
            ->get();
    }

    /**
     * Get total revenue from all paid transactions.
     *
     * @return float
     */
    public function getTotalRevenue(): float
    {
        return (float) Transaction::where('status', Transaction::STATUS_PAID)
            ->sum('grand_total');
    }

    /**
     * Get summary of transactions grouped by insurance.
     *
     * @return Collection
     */
    public function getInsuranceSummary(): Collection
    {
        return Transaction::where('status', Transaction::STATUS_PAID)
            ->selectRaw('insurance_name, COUNT(*) as total, SUM(grand_total) as revenue')
            ->groupBy('insurance_name')
            ->get();
    }

    /**
     * Get daily transaction counts.
     *
     * @return Collection
     */
    public function getDailyTransactionCount(): Collection
    {
        return Transaction::where('status', Transaction::STATUS_PAID)
            ->selectRaw('DATE(paid_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }
}
