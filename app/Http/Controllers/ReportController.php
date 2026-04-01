<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing reports.
 */
class ReportController extends Controller
{
    /**
     * @var ReportService
     */
    protected ReportService $reportService;

    /**
     * ReportController constructor.
     *
     * @param ReportService $reportService
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the reports dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $insuranceSummary = $this->reportService->getInsuranceSummary();
        $dailyCounts = $this->reportService->getDailyTransactionCount();
        $totalRevenue = $this->reportService->getTotalRevenue();

        return view('marketing.reports.index', compact(
            'insuranceSummary',
            'dailyCounts',
            'totalRevenue'
        ));
    }
}
