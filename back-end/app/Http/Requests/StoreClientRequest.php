<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la permission est déjà vérifiée par le middleware de route
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'nif_stat' => 'nullable|string|max:50',
            'informations_complementaires' => 'nullable|string',
        ];
    }
}