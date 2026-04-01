<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
     protected $fillable = [
        'transaction_id',
        'procedure_id',
        'procedure_name',
        'price',
        'discount',
        'subtotal',
    ];

     public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
