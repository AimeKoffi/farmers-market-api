<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Repayment;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class RepaymentService
{
    /**
     * Enregistre un remboursement et applique le FIFO sur les dettes ouvertes.
     */
    public function recordRepayment(Farmer $farmer, int $operatorId, float $kgReceived): Repayment
    {
        return DB::transaction(function () use ($farmer, $operatorId, $kgReceived) {

            // 1. Taux de conversion actuel (configurable)
            $commodityRate = (float) Setting::getValue('commodity_rate', 1000);
            $fcfaValue = round($kgReceived * $commodityRate, 2);

            // 2. Vérifier qu'il y a des dettes à rembourser
            $openDebts = $farmer->openDebts()->get();
            if ($openDebts->isEmpty()) {
                throw new \Exception("Cet agriculteur n'a aucune dette en cours.", 422);
            }

            // 3. Créer le remboursement
            $repayment = Repayment::create([
                'farmer_id'      => $farmer->id,
                'operator_id'    => $operatorId,
                'kg_received'    => $kgReceived,
                'commodity_rate' => $commodityRate,
                'fcfa_value'     => $fcfaValue,
            ]);

            // 4. Appliquer le FIFO
            $remaining = $fcfaValue;

            foreach ($openDebts as $debt) {
                if ($remaining <= 0) break;

                $toApply = min($remaining, (float) $debt->remaining_fcfa);

                // Enregistrer l'application sur cette dette
                $repayment->debts()->attach($debt->id, [
                    'amount_applied' => $toApply,
                ]);

                // Mettre à jour la dette
                $debt->remaining_fcfa = round((float) $debt->remaining_fcfa - $toApply, 2);
                $debt->updateStatus();

                $remaining = round($remaining - $toApply, 2);
            }

            return $repayment->load(['farmer', 'operator', 'debts']);
        });
    }
}