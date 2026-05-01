<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class RepaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'farmer'         => new FarmerResource($this->whenLoaded('farmer')),
            'operator'       => new UserResource($this->whenLoaded('operator')),
            'kg_received'    => (float) $this->kg_received,
            'commodity_rate' => (float) $this->commodity_rate,
            'fcfa_value'     => (float) $this->fcfa_value,
            'debts_affected' => $this->whenLoaded('debts', function () {
                return $this->debts->map(fn($d) => [
                    'debt_id'        => $d->id,
                    'amount_applied' => (float) $d->pivot->amount_applied,
                ]);
            }),
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}