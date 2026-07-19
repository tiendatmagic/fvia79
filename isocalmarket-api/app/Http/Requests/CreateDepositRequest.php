<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount_usdt' => 'required|numeric|min:0.1|max:100000',
            'payment_method' => 'required|string|in:bep20,binance',
            'payer_binance_id' => 'required_if:payment_method,binance|nullable|string|min:8|max:30',
        ];
    }
}
