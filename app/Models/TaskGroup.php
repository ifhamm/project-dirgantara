<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Migration
// Model
class TaskGroup extends Model
{
    protected $fillable = [
        'dock_phase_id', 'no', 'name',
        'progress', 'allocation', 'start_date', 'finish_date', 'work_days',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'finish_date' => 'date',
        'progress'    => 'decimal:6',
        'allocation'  => 'decimal:6',
    ];

    public function dockPhase()
    {
        return $this->belongsTo(DockPhase::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
