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
        'status',
        'completed_by',
        'completed_date',
        'timer_start_time',
        'attachments'
    ];

    protected $casts = [
        'plan_hours' => 'float',
        'hours' => 'float',
        'completed_date' => 'datetime',
        'details' => 'array',
        'attachments' => 'array',
        'man' => 'array'
    ];

    // Accessor supaya blade $step->mechanics tetap jalan
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
}