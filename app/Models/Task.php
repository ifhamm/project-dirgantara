<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'task_group_id', 'no', 'name',
        'progress', 'allocation', 'start_date', 'finish_date', 'work_days',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'finish_date' => 'date',
        'progress'    => 'decimal:6',
        'allocation'  => 'decimal:6',
    ];

    public function taskGroup()
    {
        return $this->belongsTo(TaskGroup::class);
    }

    public function mwsParts()
    {
        return $this->hasMany(MwsPart::class);
    }
}
