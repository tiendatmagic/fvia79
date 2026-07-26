<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class B2bBuyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('idempotency_key') && $this->hasHeader('Idempotency-Key')) {
            $this->merge([
                'idempotency_key' => $this->header('Idempotency-Key'),
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['sometimes', 'array', 'min:1', 'max:20'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'product_id' => ['required_without:items', 'uuid', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
