<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'batch_name' => 'required|string',
            'raw_data' => 'required|string',
            'cost_price' => 'nullable|numeric|min:0',
        ];
    }
}
