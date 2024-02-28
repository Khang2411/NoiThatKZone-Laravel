<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['product_id', 'user_id', 'content', 'rating', 'review_id', 'status'];
    
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }
    public function replies(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    public function product(): BelongsTo
    {
        return $this->BelongsTo(Product::class);
    }
}
