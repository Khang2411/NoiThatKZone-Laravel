<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['slug', 'name', 'describe','price', 'price_before_discount','thumbnail','is_featured','is_hot','collection_id'];

    public function collection(): BelongsTo
    {
        return $this->BelongsTo(Collection::class);
    }
    public function detailImages()
    {
        return $this->hasMany(DetailImage::class);
    }
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }
    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class);
    }
}
