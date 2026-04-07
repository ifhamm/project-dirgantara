<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'company_name',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function mwsParts()
    {
        return $this->hasMany(MwsPart::class);
    }
}
