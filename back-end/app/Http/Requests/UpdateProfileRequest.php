<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(Auth::id())],
            'current_password' => 'required_with:password|nullable',
            'password' => 'nullable|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required_with' => 'Le mot de passe actuel est requis pour en définir un nouveau.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
        ];
    }
}