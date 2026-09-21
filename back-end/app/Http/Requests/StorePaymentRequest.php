<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant' => 'required|numeric|min:0.01',
            'date_paiement' => 'required|date',
            'mode' => 'required|in:especes,virement,cheque,mobile_money,autre',
            'reference' => 'nullable|string|max:100',
            'commentaire' => 'nullable|string',
        ];
    }
}