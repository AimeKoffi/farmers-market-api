<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'transaction_id',
        'farmer_id',
        'amount_fcfa',
        'remaining_fcfa',
        'status',
    ];

    protected $casts = [
        'amount_fcfa'    => 'decimal:2',
        'remaining_fcfa' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function repayments()
    {
        return $this->belongsToMany(
            Repayment::class,
            'repayment_debt'
        )->withPivot('amount_applied');
    }

    // Met à jour le statut automatiquement après un paiement
    public function updateStatus(): void
    {
        if ((float) $this->remaining_fcfa <= 0) {
            $this->status = 'paid';
        } elseif ((float) $this->remaining_fcfa < (float) $this->amount_fcfa) {
            $this->status = 'partial';
        } else {
            $this->status = 'open';
        }
        $this->save();
    }
}