<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndockTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'code',
        'level',
        'parent_id',
        'planned_start',
        'planned_end',
        'progress',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(IndockProject::class, 'project_id');
    }

    public function parent()
    {
        return $this->belongsTo(IndockTask::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(IndockTask::class, 'parent_id');
    }

    public function mwsPart()
    {
        return $this->hasOne(MwsPart::class);
    }
}
