<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model
class DockPhase extends Model
{
    protected $fillable = [
        'project_id', 'type', 'no', 'name',
        'progress', 'allocation', 'start_date', 'finish_date', 'work_days',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'finish_date' => 'date',
        'progress'    => 'decimal:6',
        'allocation'  => 'decimal:6',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function taskGroups()
    {
        return $this->hasMany(TaskGroup::class);
    }
    
    // helper
    public function isPredock(): bool { return $this->type === 'predock'; }
    public function isIndock(): bool  { return $this->type === 'indock'; }
    public function isPostdock(): bool{ return $this->type === 'postdock'; }
}
