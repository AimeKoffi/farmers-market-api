<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'identifier'   => $this->identifier,
            'firstname'    => $this->firstname,
            'lastname'     => $this->lastname,
            'full_name'    => $this->firstname . ' ' . $this->lastname,
            'phone'        => $this->phone,
            'credit_limit' => (float) $this->credit_limit,
            'total_debt'   => $this->total_debt,
            'available_credit' => max(0, (float) $this->credit_limit - $this->total_debt),
            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}