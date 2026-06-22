<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'customer', 'contract_no', 'aircraft_reg',
        'aircraft_type', 'aircraft_series', 'description', 'progress',
        'start_date', 'finish_date', 'work_days',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'finish_date' => 'date',
        'progress'    => 'decimal:6',
    ];

    public function dockPhases()
    {
        return $this->hasMany(DockPhase::class);
    }

    public function predock()
    {
        return $this->hasOne(DockPhase::class)->where('type', 'predock');
    }

    public function indock()
    {
        return $this->hasOne(DockPhase::class)->where('type', 'indock');
    }

    public function postdock()
    {
        return $this->hasOne(DockPhase::class)->where('type', 'postdock');
    }
}
