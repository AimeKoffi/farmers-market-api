<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreRepaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'farmer_id'   => 'required|exists:farmers,id',
            'kg_received' => 'required|numeric|min:0.001',
        ];
    }
}