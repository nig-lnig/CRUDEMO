<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * Attributs autorisés pour l'assignation de masse
     * (create(), update(), fill(), firstOrCreate()...).
     */
    protected $fillable = [
        'name',
        'sku',
        'category',
        'description',
        'price',
        'stock',
        'is_active',
    ];

    /**
     * Conversion automatique des types.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
