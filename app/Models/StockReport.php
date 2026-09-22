<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'report_date',
        'stock_in',
        'source',
        'invoice_number',
        'cost_price',
        'stock_out',
        'sold',
        'damaged',
        'remaining',
        'notes',
    ];

    protected $casts = [
        'report_date' => 'date',
        'cost_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
