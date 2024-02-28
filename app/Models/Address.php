<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;
    public $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'city_id',
        'district_id',
        'ward_id',
        'apartment_number'
    ];
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }
}
