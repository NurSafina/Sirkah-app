<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'price', 'effective_from', 'effective_until', 'created_by'];
    protected $casts = ['price' => 'decimal:2', 'effective_from' => 'datetime', 'effective_until' => 'datetime'];
    public function product() { return $this->belongsTo(Product::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
