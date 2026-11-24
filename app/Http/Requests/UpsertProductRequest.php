<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation pour firstOrCreate() / updateOrCreate().
 * Le SKU n'est pas « unique » ici : c'est justement la clé de recherche.
 */
class UpsertProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
