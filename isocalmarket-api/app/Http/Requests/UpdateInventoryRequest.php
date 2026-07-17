<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'sometimes|required|exists:products,id',
            'batch_name' => 'required|string',
            'status' => 'required|in:active,completed,inactive',
            'keys_pool' => 'nullable|string',
        ];
    }
}
