<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\ApiService;
use App\Services\VoucherService;
use App\Services\ActivityLoggerService;

/**
 * Service class for handling Transaction business logic.
 */
class TransactionService
{
    /**
     * @var ApiService
     */
    protected ApiService $apiService;

    /**
     * @var VoucherService
     */
    protected VoucherService $voucherService;
    protected ActivityLoggerService $activityLogger;

    /**
     * TransactionService constructor.
     *
     * @param ApiService $apiService
     * @param VoucherService $voucherService
     * @param ActivityLoggerService $activityLogger
     */
    public function __construct(ApiService $apiService, VoucherService $voucherService, ActivityLoggerService $activityLogger)
    {
        $this->apiService = $apiService;
        $this->voucherService = $voucherService;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Create a new transaction with its details.
     *
     * @param array $data
     * @return Transaction
     * @throws \Exception
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $totalPrice = 0;
            $detailsData = [];

            foreach ($data['items'] as $item) {
                $priceData = $this->apiService->getProcedurePrices($item['procedure_id']);

                // Handle both direct array or wrapped in 'prices' key
                $prices = is_array($priceData) && isset($priceData['prices'])
                    ? $priceData['prices']
                    : $priceData;

                // Skip jika harga tidak valid
                if (!is_array($prices) || count($prices) === 0) {
                    continue;
                }

                // Ambil harga terakhir dari array prices
                $latestPriceData = end($prices);
                $unitPrice = $latestPriceData['unit_price'] ?? 0;
                $adminFee = $latestPriceData['admin_fee'] ?? 0;

                $price = (float) $unitPrice + (float) $adminFee;
                $subtotal = $price * $item['qty'];
                $totalPrice += $subtotal;

                $detailsData[] = [
                    'procedure_id' => $item['procedure_id'],
                    'procedure_name' => $item['procedure_name'],
                    'price' => $price,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                ];
            }

            // ❗ FIX: Jangan lanjut kalau semua item gagal
            if (empty($detailsData)) {
                throw new \Exception('Semua harga prosedur gagal diambil. Transaksi dibatalkan.');
            }

            // ✅ FIX: Voucher aman
            $totalDiscount = 0;
            if (!empty($data['voucher_id'])) {
                $voucher = Voucher::find($data['voucher_id']);

                if ($voucher) {
                    $totalDiscount = $this->voucherService->calculateDiscount($voucher, (float)$totalPrice);
                }
            }

            $grandTotal = max(0, $totalPrice - $totalDiscount);

            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'patient_name' => $data['patient_name'],
                'insurance_name' => $data['insurance_name'],
                'total_price' => $totalPrice,
                'total_discount' => $totalDiscount,
                'grand_total' => $grandTotal,
                'status' => Transaction::STATUS_DRAFT,
                'created_by' => auth()->id(),
            ]);

            foreach ($detailsData as $detail) {
                $transaction->details()->create($detail);
            }

            $this->activityLogger->log(
                'Create Transaction',
                'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' created transaction with Invoice Number: ' . $transaction->invoice_number,
                auth()->id()
            );

            return $transaction;
        });
    }

    /**
     * Update an existing draft transaction.
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     * @throws \Exception
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        if (!$transaction->isDraft()) {
            throw new \Exception("Transaksi yang sudah dibayar tidak dapat diubah.");
        }

        return DB::transaction(function () use ($transaction, $data) {
            $totalPrice = 0;
            $transaction->details()->delete();

            foreach ($data['items'] as $item) {
                $price = (float) $item['price'];
                $subtotal = $price * $item['qty'];
                $totalPrice += $subtotal;

                $transaction->details()->create([
                    'procedure_id' => $item['procedure_id'],
                    'procedure_name' => $item['procedure_name'],
                    'price' => $price,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                ]);
            }

            $totalDiscount = 0;
            if (isset($data['voucher_id']) && $data['voucher_id']) {
                $voucher = Voucher::find($data['voucher_id']);
                if ($voucher) {
                    $totalDiscount = $this->voucherService->calculateDiscount($voucher, (float)$totalPrice);
                }
            }

            $grandTotal = max(0, $totalPrice - $totalDiscount);

            $transaction->update([
                'patient_name' => $data['patient_name'],
                'insurance_name' => $data['insurance_name'],
                'total_price' => $totalPrice,
                'total_discount' => $totalDiscount,
                'grand_total' => $grandTotal,
            ]);

            $this->activityLogger->log(
                'Update Transaction',
                'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' updated transaction with Invoice Number: ' . $transaction->invoice_number,
                auth()->id()
            );

            return $transaction;
        });
    }

    /**
     * Mark transaction as paid.
     *
     * @param Transaction $transaction
     * @return Transaction
     * @throws \Exception
     */
    public function markAsPaid(Transaction $transaction): Transaction
    {
        if (!$transaction->isDraft()) {
            throw new \Exception("Transaksi sudah berstatus bayar.");
        }

        $transaction->update([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->activityLogger->log(
            'Mark Transaction Paid',
            'User ' . (auth()->check() ? auth()->user()->name : 'Guest') . ' marked transaction with Invoice Number: ' . $transaction->invoice_number . ' as paid.',
            auth()->id()
        );

        return $transaction;
    }
}
