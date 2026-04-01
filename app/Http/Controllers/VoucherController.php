<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Services\ApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Services\ActivityLoggerService;

/**
 * Controller for managing vouchers.
 */
class VoucherController extends Controller
{
    /**
     * @var ApiService
     */
    protected ApiService $apiService;
    protected ActivityLoggerService $activityLogger;

    /**
     * VoucherController constructor.
     *
     * @param ApiService $apiService
     * @param ActivityLoggerService $activityLogger
     */
    public function __construct(ApiService $apiService, ActivityLoggerService $activityLogger)
    {
        $this->apiService = $apiService;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Display a listing of the vouchers.
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws \Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $vouchers = Voucher::latest()->get();
            return DataTables::of($vouchers)
                ->addIndexColumn()
                ->editColumn('type', function ($item) {
                    return $item->type === 'percent' ? 'Persentase' : 'Nominal';
                })
                ->editColumn('value', function ($item) {
                    return $item->label;
                })
                ->editColumn('max_discount', function ($item) {
                    return $item->max_discount ? 'Rp ' . number_format($item->max_discount, 0, ',', '.') : '-';
                })
                ->editColumn('insurance_name', function ($item) {
                    return $item->insurance_name ?? 'Semua Asuransi';
                })
                ->editColumn('is_active', function ($item) {
                    $badge = $item->is_active ? 'badge-light-success' : 'badge-light-danger';
                    $text = $item->is_active ? 'Aktif' : 'Non-Aktif';
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->addColumn('actions', function ($item) {
                    return
                        '<div class="dropdown text-end">
                        <button type="button" class="btn btn-secondary btn-sm btn-active-light-primary rotate" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start" data-bs-toggle="dropdown">
                            Actions
                            <span class="svg-icon svg-icon-3 rotate-180 ms-3 me-0">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor"></path>
                                </svg>
                            </span>
                        </button>
                        <div class="dropdown-menu menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-100px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="' . route('voucher.edit', $item->id) . '" class="menu-link px-3">Edit</a>
                            </div>
                            <div class="menu-item px-3">
                                <a class="menu-link px-3 delete-confirm" data-id="' . $item->id . '" role="button">Hapus</a>
                            </div>
                        </div>
                    </div>';
                })
                ->rawColumns(['is_active', 'actions'])
                ->make(true);
        }
        return view('marketing.voucher.index');
    }

    /**
     * Show the form for creating a new voucher.
     *
     * @return View
     */
    public function create(): View
    {
        $insurances = $this->apiService->getInsurances();
        return view('marketing.voucher.create', compact('insurances'));
    }

    /**
     * Store a newly created voucher.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'insurance_name' => 'nullable|string',
            'type' => 'required|in:percent,nominal',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        Voucher::create($validated);

        $this->activityLogger->log(
            'Create Voucher',
            'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' created voucher: ' . $validated['name'] . ' (Type: ' . $validated['type'] . ', Value: ' . $validated['value'] . ')',
            auth()->id()
        );

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil dibuat.');
    }

    /**
     * Show the form for editing a voucher.
     *
     * @param Voucher $voucher
     * @return View
     */
    public function edit(Voucher $voucher): View
    {
        $insurances = $this->apiService->getInsurances();
        return view('marketing.voucher.edit', compact('voucher', 'insurances'));
    }

    /**
     * Update a voucher.
     *
     * @param Request $request
     * @param Voucher $voucher
     * @return RedirectResponse
     */
    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'insurance_name' => 'nullable|string',
            'type' => 'required|in:percent,nominal',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
        ]);

        $voucher->update($validated);

        $this->activityLogger->log(
            'Update Voucher',
            'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' updated voucher: ' . $validated['name'] . ' (ID: ' . $voucher->id . ')',
            auth()->id()
        );

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    /**
     * Remove a voucher.
     *
     * @param Voucher $voucher
     * @return JsonResponse
     */
    public function destroy(Voucher $voucher): JsonResponse
    {
        try {
            $voucherName = $voucher->name; // Get name before deletion
            $voucherId = $voucher->id; // Get ID before deletion
            $voucher->delete();

            $this->activityLogger->log(
                'Delete Voucher',
                'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' deleted voucher: ' . $voucherName . ' (ID: ' . $voucherId . ')',
                auth()->id()
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Voucher berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
