<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LoggedCloud\PageStudio\Attributes\ExposeToModelFinder;

/**
 * E-commerce example · authors look up a product by SKU and render
 * a marketing page from it. The `findBy` list intentionally
 * includes `id` (admin URLs) and `sku` (customer-facing).
 */
#[ExposeToModelFinder(
    label:      'Product',
    findBy:     ['id', 'sku'],
    searchable: ['name', 'sku', 'description'],
    expose:     ['id', 'sku', 'name', 'description', 'price_cents', 'currency', 'image_url'],
)]
class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
        ];
    }
}
