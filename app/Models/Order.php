<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'method', 'status', 'email', 'phone', 'ship_address', 'ship_name', 'city_id', 'district_id', 'ward_id', 'discount', "coupon_code"];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'price');
    }

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): belongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): belongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ward(): belongsTo
    {
        return $this->belongsTo(Ward::class);
    }
}
