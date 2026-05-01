<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'farmer'              => new FarmerResource($this->whenLoaded('farmer')),
            'operator'            => new UserResource($this->whenLoaded('operator')),
            'items'               => TransactionItemResource::collection($this->whenLoaded('items')),
            'total_fcfa'          => (float) $this->total_fcfa,
            'payment_method'      => $this->payment_method,
            'interest_rate'       => (float) $this->interest_rate,
            'interest_amount'     => (float) $this->total_with_interest - (float) $this->total_fcfa,
            'total_with_interest' => (float) $this->total_with_interest,
            'debt'                => new DebtResource($this->whenLoaded('debt')),
            'created_at'          => $this->created_at->toDateTimeString(),
        ];
    }
}