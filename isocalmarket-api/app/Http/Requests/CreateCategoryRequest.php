<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_vi' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $this->route('id'),
            'description' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
