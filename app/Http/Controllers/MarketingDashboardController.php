<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarketingDashboardController extends Controller
{
    public function index()
    {

        $stats = [
            'total_visits' => Transaction::where('status', Transaction::STATUS_PAID)->count(),
            'total_revenue' => Transaction::where('status', Transaction::STATUS_PAID)->sum('grand_total'),
            'active_vouchers' => Voucher::where('is_active', true)->count(),
            'voucher_usage' => Transaction::whereNotNull('voucher_id')->where('status', Transaction::STATUS_PAID)->count(),
        ];

        // 2. Top Asuransi berdasarkan Kunjungan (Paid only)
        $topInsurancesVisits = Transaction::where('status', Transaction::STATUS_PAID)
            ->select('insurance_name', DB::raw('count(*) as total'))
            ->groupBy('insurance_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 3. Top Asuransi berdasarkan Pendapatan
        $topInsurancesRevenue = Transaction::where('status', Transaction::STATUS_PAID)
            ->select('insurance_name', DB::raw('sum(grand_total) as total'))
            ->groupBy('insurance_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 4. Tren Kunjungan 6 Bulan Terakhir
        $monthlyTrend = Transaction::where('status', Transaction::STATUS_PAID)
            ->select(
                DB::raw("to_char(paid_at, 'Mon YYYY') as month"),
                DB::raw('count(*) as total')
            )
            ->where('paid_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->get();

        return view('dashboard', compact(
            'stats',
            'topInsurancesVisits',
            'topInsurancesRevenue',
            'monthlyTrend'
        ));
    }
}
