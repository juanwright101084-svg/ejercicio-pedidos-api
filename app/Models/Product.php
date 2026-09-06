<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Product",
    required: ["name", "price", "stock", "category_id"],
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Coca Cola 600ml"),
        new OA\Property(property: "price", type: "number", format: "float", example: 1.5),
        new OA\Property(property: "stock", type: "integer", example: 100),
        new OA\Property(property: "is_featured", type: "boolean", example: true),
        new OA\Property(property: "category_id", type: "integer", example: 1),
    ]
)]
class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'stock', 'is_featured', 'category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}