<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Crée une transaction complète (cash ou crédit).
     * Gère : calcul du total, intérêt, vérification limite, création de la dette.
     */
    public function createTransaction(Farmer $farmer, int $operatorId, array $items, string $paymentMethod): Transaction
    {
        return DB::transaction(function () use ($farmer, $operatorId, $items, $paymentMethod) {

            // 1. Calculer le total FCFA
            $totalFcfa = $this->calculateTotal($items);

            // 2. Appliquer l'intérêt si crédit
            $interestRate = 0;
            $totalWithInterest = $totalFcfa;

            if ($paymentMethod === 'credit') {
                $interestRate = (float) Setting::getValue('interest_rate', 0.30);
                $totalWithInterest = round($totalFcfa * (1 + $interestRate), 2);

                // 3. Vérifier la limite de crédit
                if (!$farmer->canTakeCredit($totalWithInterest)) {
                    $available = max(0, (float) $farmer->credit_limit - $farmer->total_debt);
                    throw new \Exception(
                        "Limite de crédit dépassée. Disponible : {$available} FCFA, Requis : {$totalWithInterest} FCFA.",
                        422
                    );
                }
            }

            // 4. Créer la transaction
            $transaction = Transaction::create([
                'farmer_id'           => $farmer->id,
                'operator_id'         => $operatorId,
                'total_fcfa'          => $totalFcfa,
                'payment_method'      => $paymentMethod,
                'interest_rate'       => $interestRate,
                'total_with_interest' => $totalWithInterest,
            ]);

            // 5. Enregistrer les lignes produits
            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'], // prix snapshotté
                ]);
            }

            // 6. Créer la dette si crédit
            if ($paymentMethod === 'credit') {
                Debt::create([
                    'transaction_id' => $transaction->id,
                    'farmer_id'      => $farmer->id,
                    'amount_fcfa'    => $totalWithInterest,
                    'remaining_fcfa' => $totalWithInterest,
                    'status'         => 'open',
                ]);
            }

            return $transaction->load(['farmer', 'operator', 'items.product', 'debt']);
        });
    }

    /**
     * Calcule le total d'une liste d'items.
     * unit_price vient du frontend (prix affiché), on vérifie qu'il correspond au produit.
     */
    private function calculateTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        return round($total, 2);
    }
}