<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory; // <-- HARUS ADA

    protected $fillable = [
        'workshop_uuid',
        'user_id',
        'report_type',
        'report_data',
        'photo',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
