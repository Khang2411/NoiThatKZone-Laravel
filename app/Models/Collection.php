<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['collection_id', 'slug', 'name', 'thumbnail', 'banner', 'public_id_thumbnail', 'public_id_banner'];

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
    public function rootCollection(): BelongsTo
    {
        return $this->BelongsTo(Collection::class, 'collection_id');
    }
}
