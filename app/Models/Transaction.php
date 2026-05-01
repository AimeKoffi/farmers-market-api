<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'farmer_id',
        'operator_id',
        'total_fcfa',
        'payment_method',
        'interest_rate',
        'total_with_interest',
    ];

    protected $casts = [
        'total_fcfa'           => 'decimal:2',
        'interest_rate'        => 'decimal:4',
        'total_with_interest'  => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function debt()
    {
        return $this->hasOne(Debt::class);
    }

    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function isCredit(): bool
    {
        return $this->payment_method === 'credit';
    }
}