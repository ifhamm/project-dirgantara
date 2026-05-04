<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndockProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'projectCode',
        'aircraft',
        'checkType',
        'startDate',
        'endDate',
        'status',
    ];

    public function tasks()
    {
        return $this->hasMany(IndockTask::class, 'project_id');
    }
}
