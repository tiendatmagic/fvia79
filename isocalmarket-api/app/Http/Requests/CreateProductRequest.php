<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $this->route('id'),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'status' => 'required|in:active,inactive',
            'attributes' => 'nullable|array',
            'attributes.*.label' => 'required|string|max:100',
            'attributes.*.value' => 'required|string|max:255',
        ];
    }
}
