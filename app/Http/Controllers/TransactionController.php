<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\ApiService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;
use App\Services\ActivityLoggerService;

/**
 * Controller for managing transactions.
 * Following PSR-12 standards and Service Design Pattern.
 */
class TransactionController extends Controller
{
    /**
     * @var ApiService
     */
    protected ApiService $apiService;

    /**
     * @var TransactionService
     */
    protected TransactionService $transactionService;
    protected ActivityLoggerService $activityLogger;

    /**
     * TransactionController constructor.
     *
     * @param ApiService $apiService
     * @param TransactionService $transactionService
     * @param ActivityLoggerService $activityLogger
     */
    public function __construct(ApiService $apiService, TransactionService $transactionService, ActivityLoggerService $activityLogger)
    {
        $this->apiService = $apiService;
        $this->transactionService = $transactionService;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Display a listing of the transactions.
     *
     * @param Request $request
     * @return View|JsonResponse
     * @throws \Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $transactions = Transaction::latest()->get();
            return DataTables::of($transactions)
                ->addIndexColumn()
                ->editColumn('grand_total', function ($item) {
                    return 'Rp ' . number_format($item->grand_total, 0, ',', '.');
                })
                ->editColumn('status', function ($item) {
                    $badgeClass = $item->status === Transaction::STATUS_PAID ? 'badge-light-success' : 'badge-light-warning';
                    $statusLabel = $item->status === Transaction::STATUS_PAID ? 'Lunas' : 'Draf';
                    return '<span class="badge ' . $badgeClass . ' fw-bold">' . $statusLabel . '</span>';
                })
                ->addColumn('actions', function ($item) {
                    $actions = '<div class="dropdown text-end">
                        <button type="button" class="btn btn-secondary btn-sm btn-active-light-primary rotate" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start" data-bs-toggle="dropdown">
                            Actions
                            <span class="svg-icon svg-icon-3 rotate-180 ms-3 me-0">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor"></path>
                                </svg>
                            </span>
                        </button>
                        <div class="dropdown-menu menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-150px py-4" data-kt-menu="true">';

                    if ($item->status === Transaction::STATUS_DRAFT) {
                        $actions .= '<div class="menu-item px-3">
                                <a href="' . route('transactions.edit', $item->id) . '" class="menu-link px-3">Edit Transaksi</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 btn-pay" data-id="' . $item->id . '">Proses Bayar</a>
                            </div>
                            <div class="menu-item px-3">
                                <a class="menu-link px-3 delete-confirm" data-id="' . $item->id . '" role="button">Hapus</a>
                            </div>';
                    } else {
                        $actions .= '<div class="menu-item px-3">
                                <a href="' . route('transactions.print', $item->id) . '" class="menu-link px-3" target="_blank">Cetak PDF</a>
                            </div>';
                    }

                    $actions .= '</div></div>';
                    return $actions;
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('kasir.transaction.index');
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @return View
     */
    public function create(): View
    {
        $insurances = $this->apiService->getInsurances();
        $procedures = $this->apiService->getProcedures();
        $vouchers = Voucher::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })->get();

        return view('kasir.transaction.create', compact('insurances', 'procedures', 'vouchers'));
    }

    /**
     * Store a newly created transaction.
     *
     * @param Request $request
     * @return RedirectResponse
     */
        public function store(Request $request): RedirectResponse
{
    // 🔥 DEBUG 1: lihat request masuk (aktifin kalau perlu)
    // dd($request->all());

    try {

        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'insurance_name' => 'required|string',

            'items' => 'required|array|min:1',
            'items.*.procedure_id' => 'required',
            'items.*.procedure_name' => 'nullable|string',

            'items.*.price' => 'nullable|numeric',
            'items.*.qty' => 'required|integer|min:1',

            'voucher_id' => 'nullable',
        ]);

        // 🔥 DEBUG 2: lihat hasil validasi
        // dd($validated);

    } catch (\Illuminate\Validation\ValidationException $e) {

        // 🔥 INI YANG PALING PENTING
        dd($e->errors());
    }

    try {
        // 🔥 FILTER item kosong
        $validated['items'] = array_filter($validated['items'], function ($item) {
            return !empty($item['procedure_id']) && !empty($item['qty']);
        });

        if (empty($validated['items'])) {
            return back()
                ->with('error', 'Minimal 1 tindakan harus dipilih.')
                ->withInput();
        }

        // 🔥 FIX voucher biar aman
        if (empty($validated['voucher_id'])) {
            $validated['voucher_id'] = null;
        }

        $transaction = $this->transactionService->createTransaction($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil disimpan (Draft): ' . $transaction->invoice_number);

    } catch (\Exception $e) {

        // 🔥 DEBUG 3: error asli backend
        dd($e->getMessage(), $e->getTraceAsString());

        return back()
            ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Fetch procedure price via AJAX.
     *
     * @param mixed $id
     * @return JsonResponse
     */
    public function getProcedurePrice($id): JsonResponse
    {
        $prices = $this->apiService->getProcedurePrices($id);

        if (!$prices || !is_array($prices) || count($prices) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data harga dari API'
            ], 500);
        }

        $latestPriceData = end($prices);
        $unitPrice = $latestPriceData['unit_price'] ?? 0;
        $adminFee = $latestPriceData['admin_fee'] ?? 0;

        $totalPrice = (float) $unitPrice + (float) $adminFee;

        return response()->json([
            'success' => true,
            'price' => $totalPrice,
            'original_price' => $unitPrice,
            'admin_fee' => $adminFee
        ]);
    }

    /**
     * Show the form for editing a transaction.
     *
     * @param Transaction $transaction
     * @return View|RedirectResponse
     */
    public function edit(Transaction $transaction): View|RedirectResponse
    {
        if (!$transaction->isDraft()) {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi yang sudah dibayar tidak dapat diubah.');
        }

        $insurances = $this->apiService->getInsurances();
        $procedures = $this->apiService->getProcedures();
        $vouchers = Voucher::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })->get();

        $transaction->load('details');

        return view('kasir.transaction.edit', compact('transaction', 'insurances', 'procedures', 'vouchers'));
    }

    /**
     * Update a transaction.
     *
     * @param Request $request
     * @param Transaction $transaction
     * @return RedirectResponse
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'insurance_name' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.procedure_id' => 'required',
            'items.*.procedure_name' => 'required',
            'items.*.price' => 'required|numeric',
            'items.*.qty' => 'required|integer|min:1',
            'voucher_id' => 'nullable|exists:vouchers,id',
        ]);

        try {
            $this->transactionService->updateTransaction($transaction, $validated);
            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mark transaction as paid.
     *
     * @param Transaction $transaction
     * @return JsonResponse
     */
    public function markAsPaid(Transaction $transaction): JsonResponse
    {
        try {
            $this->transactionService->markAsPaid($transaction);
            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil dikonfirmasi!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print PDF.
     *
     * @param Transaction $transaction
     * @return mixed
     */
    public function printPdf(Transaction $transaction): mixed
    {
        $transaction->load(['details', 'user']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kasir.transaction.pdf', compact('transaction'));

        $this->activityLogger->log(
            'Print Transaction',
            'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' printed transaction with Invoice Number: ' . $transaction->invoice_number,
            auth()->id()
        );

        return $pdf->stream($transaction->invoice_number . '.pdf');
    }

    /**
     * Remove a transaction.
     *
     * @param Transaction $trans
     *
     * action
     * @return JsonResponse
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        if (!$transaction->isDraft()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi yang sudah dibayar tidak dapat dihapus.',
            ], 403);
        }

        try {
            $invoiceNumber = $transaction->invoice_number; // Get invoice number before deletion
            $transactionId = $transaction->id; // Get ID before deletion
            $transaction->delete();

            $this->activityLogger->log(
                'Delete Transaction',
                'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' deleted transaction with Invoice Number: ' . $invoiceNumber . ' (ID: ' . $transactionId . ')',
                auth()->id()
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
