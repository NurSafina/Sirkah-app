<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'category',
        'category_id',
        'unit',
        'description',
        'price',
        'cost_price',
        'stock',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function categoryRelation()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
