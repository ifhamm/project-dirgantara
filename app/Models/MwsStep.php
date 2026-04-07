<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MwsStep extends Model
{
    use HasFactory;

    protected $table = 'mws_steps';

    protected $fillable = [
        'mws_part_id', 'no', 'description', 'details', 'planMan', 'planHours', 'man', 'hours', 'tech', 'insp',
        'status', 'completedBy', 'completedDate', 'timer_start_time', 'attachments'
    ];

    public function mwsPart()
    {
        return $this->belongsTo(MwsPart::class);
    }
}
