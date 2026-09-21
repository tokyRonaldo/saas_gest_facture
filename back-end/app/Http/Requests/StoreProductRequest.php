<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'nom' => 'required|string|max:255',
            'reference' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'reference')->ignore($productId),
            ],
            'description' => 'nullable|string',
            'prix_ht' => 'required|numeric|min:0',
            'tva' => 'required|numeric|min:0|max:100',
            'unite' => 'required|string|max:50',
            'statut' => 'required|in:actif,inactif',
        ];
    }
}