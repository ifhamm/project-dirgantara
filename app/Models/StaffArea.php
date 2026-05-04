<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'name',
        'assignedCustomers',
        'assignedShopArea',
    ];

    protected $casts = [
        'assignedCustomers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
