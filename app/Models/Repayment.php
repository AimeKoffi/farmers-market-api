<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    protected $fillable = [
        'farmer_id',
        'operator_id',
        'kg_received',
        'commodity_rate',
        'fcfa_value',
    ];

    protected $casts = [
        'kg_received'    => 'decimal:3',
        'commodity_rate' => 'decimal:2',
        'fcfa_value'     => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    // Dettes touchées par ce remboursement (avec montant appliqué)
    public function debts()
    {
        return $this->belongsToMany(
            Debt::class,
            'repayment_debt'
        )->withPivot('amount_applied');
    }
}