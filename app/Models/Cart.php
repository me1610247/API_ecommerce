<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Cart extends Model
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'price'
    ];

    // Relationship with Product model
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship with User model (if needed)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
  
}
