<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        if (! $isUpdate || $this->has('name') || $this->has('name_en') || $this->has('name_vi')) {
            $nameEn = $this->filled('name_en')
                ? trim((string) $this->input('name_en'))
                : trim((string) $this->input('name', ''));
            $nameVi = $this->filled('name_vi')
                ? trim((string) $this->input('name_vi'))
                : ($nameEn ?: trim((string) $this->input('name', '')));

            if ($nameEn !== '' || $nameVi !== '') {
                $finalName = $nameEn ?: $nameVi;
                $this->merge([
                    'name' => $finalName,
                    'name_en' => $nameEn ?: $finalName,
                    'name_vi' => $nameVi ?: $finalName,
                ]);
            }
        }

        if (! $isUpdate || $this->has('description') || $this->has('description_en') || $this->has('description_vi')) {
            $descriptionEn = $this->filled('description_en')
                ? $this->input('description_en')
                : $this->input('description');
            $descriptionVi = $this->filled('description_vi')
                ? $this->input('description_vi')
                : $descriptionEn;

            if ($descriptionEn !== null || $descriptionVi !== null) {
                $this->merge([
                    'description' => $descriptionEn,
                    'description_en' => $descriptionEn,
                    'description_vi' => $descriptionVi,
                ]);
            }
        }

        if ($this->has('category_id')) {
            $catId = $this->input('category_id');
            if ($catId === '' || $catId === 'none' || $catId === 'null') {
                $this->merge(['category_id' => null]);
            }
        }

        if ($this->has('slug')) {
            $slug = trim((string) $this->input('slug'));
            if ($slug === '') {
                $this->merge(['slug' => null]);
            } else {
                $this->merge(['slug' => $slug]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $req = $isUpdate ? 'sometimes|required' : 'required';

        return [
            'name' => "{$req}|string|max:255",
            'name_vi' => "{$req}|string|max:255",
            'name_en' => "{$req}|string|max:255",
            'slug' => 'nullable|string|max:255|unique:products,slug,'.$this->route('id'),
            'description' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => "{$req}|numeric|min:0",
            'is_manual_price' => 'nullable|boolean',
            'manual_price' => 'nullable|numeric|min:0',
            'manual_price_currency' => 'nullable|string|in:VND,USDT,USD',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'status' => "{$req}|in:active,inactive",
            'attributes' => 'nullable|array',
            'attributes.*.label' => 'required|string|max:100',
            'attributes.*.value' => 'required|string|max:255',
        ];
    }
}
