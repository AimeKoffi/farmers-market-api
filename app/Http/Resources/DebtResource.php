<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'transaction_id' => $this->transaction_id,
            'amount_fcfa'    => (float) $this->amount_fcfa,
            'remaining_fcfa' => (float) $this->remaining_fcfa,
            'paid_fcfa'      => (float) $this->amount_fcfa - (float) $this->remaining_fcfa,
            'status'         => $this->status,
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}