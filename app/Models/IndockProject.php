<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndockProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'aircraft',
        'check_type',
        'start_date',
        'end_date',
        'status',
    ];

    public function tasks()
    {
        return $this->hasMany(IndockTask::class, 'project_id');
    }
}
