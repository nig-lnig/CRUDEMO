<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation d'un lot de produits pour insert().
 * Toutes les lignes doivent avoir exactement les mêmes colonnes.
 */
class InsertProductsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'products' => ['required', 'array', 'min:1', 'max:20'],
            'products.*.name' => ['required', 'string', 'max:120'],
            'products.*.sku' => ['required', 'string', 'max:40', 'distinct', 'unique:products,sku'],
            'products.*.category' => ['required', 'string', 'max:60'],
            'products.*.price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'products.*.stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
