<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number','brand','type','owner_name','status','last_active_at'
    ];
    protected $casts = ['last_active_at' => 'datetime'];
}
