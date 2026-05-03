<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MwsPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'iwo_no',
        'part_number',
        'serial_number',
        'title',
        'ref',
        'job_type',
        'ac_type',
        'wbs_no',
        'worksheet_no',
        'shop_area',
        'revision',
        'zone',
        'status',
        'start_date',
        'finish_date',
        'is_urgent',
        'urgent_request',
        'current_step',
        'men_powers',
        'total_duration',
        'stripping_date',
        'stripping_deadline',
        'stripping_percentage',
        'preparedBy',
        'preparedAt',
        'approvedBy',
        'approvedAt',
        'verifiedBy',
        'verifiedAt',
        'status_s_us',
        'customer_id',
        'indock_task_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'finish_date' => 'date',
        'is_urgent' => 'boolean',
        'urgent_request' => 'boolean',
        'stripping_date' => 'date',
        'stripping_deadline' => 'date',
        'stripping_percentage' => 'integer',
        'preparedAt' => 'datetime',
        'approvedAt' => 'datetime',
        'verifiedAt' => 'datetime',
    ];

    public function steps()
    {
        return $this->hasMany(MwsStep::class, 'mws_part_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function consumables()
    {
        return $this->hasMany(MwsConsumable::class, 'mws_part_id')->orderBy('order');
    }
}