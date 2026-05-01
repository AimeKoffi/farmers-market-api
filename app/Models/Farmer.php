<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'firstname',
        'lastname',
        'phone',
        'credit_limit',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class);
    }

    // Dettes encore ouvertes (open ou partial)
    public function openDebts()
    {
        return $this->hasMany(Debt::class)
                    ->whereIn('status', ['open', 'partial'])
                    ->orderBy('created_at', 'asc'); // FIFO
    }

    // Total de la dette courante
    public function getTotalDebtAttribute(): float
    {
        return (float) $this->debts()
                            ->whereIn('status', ['open', 'partial'])
                            ->sum('remaining_fcfa');
    }

    // Vérifie si un nouveau montant dépasserait la limite
    public function canTakeCredit(float $amount): bool
    {
        return ($this->total_debt + $amount) <= (float) $this->credit_limit;
    }
}