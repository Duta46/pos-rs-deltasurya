<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Controller for handling dashboard requests.
 * Implementing Dependency Injection for DashboardService.
 */
class DashboardController extends Controller
{
    /**
     * @var DashboardService
     */
    protected DashboardService $dashboardService;

    /**
     * DashboardController constructor.
     *
     * @param DashboardService $dashboardService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Fetch data using DashboardService (Service Pattern)
        $stats = $this->dashboardService->getSummaryStats();
        $topInsurancesVisits = $this->dashboardService->getTopInsurancesByVisits();
        $topInsurancesRevenue = $this->dashboardService->getTopInsurancesByRevenue();
        $monthlyTrend = $this->dashboardService->getMonthlyVisitTrend();

        return view('dashboard', compact(
            'stats',
            'topInsurancesVisits',
            'topInsurancesRevenue',
            'monthlyTrend'
        ));
    }
}
