<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['title', 'user_id', 'thumbnail', 'content', 'slug', 'public_id_thumbnail'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
