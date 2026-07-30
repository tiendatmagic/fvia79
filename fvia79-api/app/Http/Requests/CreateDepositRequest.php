<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepositRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_usdt' => 'required|numeric|min:0.1|max:100000',
            'payment_method' => 'required|string|in:bep20,binance',
            'payer_binance_id' => 'nullable|string|max:50',
        ];
    }
}
