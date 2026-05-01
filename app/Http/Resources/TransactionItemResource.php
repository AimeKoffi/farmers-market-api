<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product'   => new ProductResource($this->whenLoaded('product')),
            'quantity'  => $this->quantity,
            'unit_price'=> (float) $this->unit_price,
            'subtotal'  => $this->subtotal,
        ];
    }
}