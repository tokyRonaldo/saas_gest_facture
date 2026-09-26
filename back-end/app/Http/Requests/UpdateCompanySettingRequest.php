<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_entreprise' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'nif_stat' => 'nullable|string|max:50',
            'devise' => 'required|string|max:10',
            'conditions_paiement' => 'nullable|string',
        ];
    }
}