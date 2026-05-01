<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'price_fcfa'  => (float) $this->price_fcfa,
            'description' => $this->description,
            'category'    => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}