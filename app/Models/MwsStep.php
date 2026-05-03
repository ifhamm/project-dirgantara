<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MwsStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'mws_part_id',
        'no',
        'description',
        'details',
        'plan_man',
        'plan_hours',
        'man',
        'hours',
        'tech',
        'insp',
        'status',
        'status_s_us',
        'completed_by',
        'completed_date',
        'timer_start_time',
        'attachments',
        'caution',
        'note'
    ];

    protected $casts = [
        'plan_hours' => 'float',
        'completed_date' => 'datetime',
        'timer_start_time' => 'datetime',
        'details' => 'array',
        'attachments' => 'array',
        'man' => 'array'
    ];

public function getHoursAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return '00:00';
        }
        
        if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            $totalMinutes = (int) ($value * 60);
            $h = floor($totalMinutes / 60);
            $m = $totalMinutes % 60;
            return sprintf('%02d:%02d', $h, $m);
        }
        
        return '00:00';
    }

    public function setHoursAttribute($value)
    {
        if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
            $this->attributes['hours'] = $value;
        } elseif (is_numeric($value)) {
            // Konversi float ke HH:MM
            $totalMinutes = (int) ($value * 60);
            $h = floor($totalMinutes / 60);
            $m = $totalMinutes % 60;
            $this->attributes['hours'] = sprintf('%02d:%02d', $h, $m);
        } else {
            $this->attributes['hours'] = '00:00';
        }
    }

    public function getMechanicsAttribute()
    {
        $man = $this->man ?? [];
        $mechanics = [];
        
        foreach ($man as $nik) {
            $user = \App\Models\User::where('nik', $nik)->first();
            if ($user) {
                $mechanics[] = (object)[
                    'nik' => $user->nik,
                    'name' => $user->name
                ];
            } else {
                $mechanics[] = (object)[
                    'nik' => $nik,
                    'name' => $nik
                ];
            }
        }
        
        return collect($mechanics);
    }

    public function part()
    {
        return $this->belongsTo(MwsPart::class, 'mws_part_id');
    }

    public function subSteps()
    {
        return $this->hasMany(MwsSubStep::class, 'mws_step_id')->orderBy('order');
    }
}