<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $nameEn = $this->filled('name_en')
            ? trim((string) $this->input('name_en'))
            : trim((string) $this->input('name', ''));
        $nameVi = $this->filled('name_vi')
            ? trim((string) $this->input('name_vi'))
            : $nameEn;
        $descriptionEn = $this->filled('description_en')
            ? $this->input('description_en')
            : $this->input('description');
        $descriptionVi = $this->filled('description_vi')
            ? $this->input('description_vi')
            : $descriptionEn;

        $this->merge([
            'name' => $nameEn,
            'name_en' => $nameEn,
            'name_vi' => $nameVi,
            'description' => $descriptionEn,
            'description_en' => $descriptionEn,
            'description_vi' => $descriptionVi,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_vi' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$this->route('id'),
            'description' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
