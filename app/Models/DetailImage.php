<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailImage extends Model
{
    use HasFactory;
    protected $fillable = [
        "product_id", "image", "public_id_image"

    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
