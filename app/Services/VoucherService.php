<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Voucher;
use Carbon\Carbon;

/**
 * Service class for handling Voucher calculations and logic.

 */
class VoucherService
{
    /**
     * Calculate discount based on voucher type and total amount.
     *
     * @param Voucher|null $voucher
     * @param float $amount
     * @return float
     */
    public function calculateDiscount(?Voucher $voucher, float $amount): float
    {
        if (!$voucher || !$voucher->is_active) {
            return 0.0;
        }

        // Check active dates
        $now = Carbon::now();
        if ($voucher->start_date && $now->lt($voucher->start_date)) {
            return 0.0;
        }

        if ($voucher->end_date && $now->gt($voucher->end_date)) {
            return 0.0;
        }

        if ($voucher->type === 'percent') {
            $discount = ($voucher->value / 100) * $amount;

            if ($voucher->max_discount) {
                return (float) min($discount, $voucher->max_discount);
            }

            return (float) $discount;
        }

        if ($voucher->type === 'nominal') {
            return (float) $voucher->value;
        }

        return 0.0;
    }
}
