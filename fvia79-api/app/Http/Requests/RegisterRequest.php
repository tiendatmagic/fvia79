<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'name'     => 'nullable|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique'  => 'Username already taken.',
            'username.regex'   => 'Username must only contain letters, numbers, and underscores.',
            'username.min'     => 'Username must be at least 3 characters.',
            'email.unique'     => 'Email already registered.',
            'email.email'      => 'Email must be a valid email address.',
            'password.min'     => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
