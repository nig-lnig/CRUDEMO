<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Jeu de données déterministe : chaque démonstration est prévisible et répétable.
 */
class ProductSeeder extends Seeder
{
    public const PRODUCTS = [
        ['id' => 1,  'name' => 'Ordinateur portable Pro 14', 'sku' => 'INF-001', 'category' => 'Informatique', 'price' => 1299.00, 'stock' => 8,  'description' => 'Portable 14 pouces, 16 Go de RAM, SSD 512 Go.'],
        ['id' => 2,  'name' => 'Écran 27 pouces 4K',         'sku' => 'INF-002', 'category' => 'Informatique', 'price' => 449.90,  'stock' => 15, 'description' => 'Dalle IPS, USB-C 65 W.'],
        ['id' => 3,  'name' => 'Clavier mécanique',          'sku' => 'ACC-001', 'category' => 'Accessoires',  'price' => 119.00,  'stock' => 32, 'description' => 'Switchs tactiles, disposition AZERTY.'],
        ['id' => 4,  'name' => 'Souris ergonomique',         'sku' => 'ACC-002', 'category' => 'Accessoires',  'price' => 59.90,   'stock' => 9,  'description' => 'Souris verticale sans fil.'],
        ['id' => 5,  'name' => 'Casque à réduction de bruit', 'sku' => 'AUD-001', 'category' => 'Audio',       'price' => 279.00,  'stock' => 12, 'description' => 'Bluetooth 5.3, 30 h d\'autonomie.'],
        ['id' => 6,  'name' => 'Enceinte Bluetooth',         'sku' => 'AUD-002', 'category' => 'Audio',        'price' => 89.90,   'stock' => 0,  'description' => 'Étanche IP67.'],
        ['id' => 7,  'name' => 'Webcam Full HD',             'sku' => 'ACC-003', 'category' => 'Accessoires',  'price' => 74.50,   'stock' => 5,  'description' => '1080p, micro intégré.'],
        ['id' => 8,  'name' => 'Bureau assis-debout',        'sku' => 'MOB-001', 'category' => 'Mobilier',     'price' => 549.00,  'stock' => 3,  'description' => 'Plateau 140 × 70 cm, double moteur.'],
        ['id' => 9,  'name' => 'Chaise ergonomique',         'sku' => 'MOB-002', 'category' => 'Mobilier',     'price' => 329.00,  'stock' => 7,  'description' => 'Soutien lombaire réglable.'],
        ['id' => 10, 'name' => 'Disque SSD 1 To',            'sku' => 'INF-003', 'category' => 'Informatique', 'price' => 99.90,   'stock' => 60, 'description' => 'NVMe PCIe 4.0.'],
        ['id' => 11, 'name' => 'Microphone USB studio',      'sku' => 'AUD-003', 'category' => 'Audio',        'price' => 139.00,  'stock' => 2,  'description' => 'Cardioïde, sortie casque.'],
        ['id' => 12, 'name' => 'Hub USB-C 7 ports',          'sku' => 'ACC-004', 'category' => 'Accessoires',  'price' => 45.00,   'stock' => 0,  'description' => 'Modèle retiré du catalogue.'],
    ];

    /** Produit déjà placé dans la corbeille (démonstrations SoftDeletes / restore). */
    public const TRASHED_ID = 12;

    public function run(): void
    {
        foreach (self::PRODUCTS as $attributes) {
            // 'id' n'est pas fillable : on le force explicitement pour garder des ID stables.
            Product::factory()->make($attributes)->forceFill(['id' => $attributes['id']])->save();
        }

        Product::findOrFail(self::TRASHED_ID)->delete();
    }
}
