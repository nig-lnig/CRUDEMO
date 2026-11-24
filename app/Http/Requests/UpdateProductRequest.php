<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Règles de validation pour la modification partielle d'un produit.
 * « sometimes » : le champ n'est validé que s'il est envoyé.
 */
class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'sku' => [
                'sometimes', 'string', 'max:40',
                Rule::unique('products', 'sku')->ignore($this->route('id')),
            ],
            'category' => ['sometimes', 'string', 'max:60'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:99999999'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
