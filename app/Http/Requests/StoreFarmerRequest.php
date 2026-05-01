<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreFarmerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'identifier'   => 'required|string|unique:farmers,identifier',
            'firstname'    => 'required|string|max:100',
            'lastname'     => 'required|string|max:100',
            'phone'        => 'required|string|unique:farmers,phone',
            'credit_limit' => 'required|numeric|min:0',
        ];
    }
}