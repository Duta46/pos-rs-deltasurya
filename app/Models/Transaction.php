<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const STATUS_DRAFT = 'draf';
    const STATUS_PAID = 'paid';

    protected $fillable = [
        'invoice_number',
        'patient_name',
        'insurance_name',
        'total_price',
        'total_discount',
        'grand_total',
        'status',
        'paid_at',
        'created_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'grand_total' => 'float',
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function getFormattedGrandTotalAttribute()
    {
        return number_format($this->grand_total, 0, ',', '.');
    }
}
