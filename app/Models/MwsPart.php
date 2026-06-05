<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MwsPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'urgent_request_by',
        'start_date',
        'ref_logistic_ppc',
        'customer_name',
        'wbs_no',
        'title',
        'part_number',
        'serial_number',
        'job_type',
        'mdr_doc_deffect',
        'ref',
        'ac_type',
        'iwo_no',
        'shop_area',
        'iwo_date',
        'worksheet_no',
        'remark_mws',
        'test_result',
        'schedule_delivery_on_time',
        'ecd_finish_workdays',
        'selisih_work_days',
        'presentasi_schedule',
        'worksheet_date',
        'approved_date',
        'form_out_no',
        'tanda_terima_fo_no',
        'tanda_terima_fo_date',
        'stripping_report_date',
        'stripping_order_by_sap_date',
        'selisih_order_work_days',
        'time_stripping_work_days',
        'max_stripping_date',
        'tase_stripping',
        'presentase_bdp',
        'qty_bdp',
        'revision',
        'finish_date',
        'finish_date_2',
        'men_powers',
        'man_hours',
        'document_penyerta',
        'ship_transfers_tt_date',
        'ship_transfers_tt_no',
        'isr_no',
        'selisih_shipping_work_days',
        'tase',
        'remark',
        'prepared_by',
        'prepared_date',
        'approved_by',
        'verified_date',
        'stripping_notified',
        'capability',
        'attachment',
        'status',
        'is_urgent',
        'urgent_request',
        'current_step',
        'selisih_stripping_work_days',
        'zone',
        'testcase',
        'task_id',
        'verified_by',
        'verified_at',
        'status_s_us'
    ];

    protected $casts = [
        'start_date' => 'date',
        'iwo_date' => 'date',
        'schedule_delivery_on_time' => 'date',
        'worksheet_date' => 'date',
        'approved_date' => 'date',
        'tanda_terima_fo_date' => 'date',
        'stripping_report_date' => 'date',
        'stripping_order_by_sap_date' => 'date',
        'max_stripping_date' => 'date',
        'finish_date' => 'date',
        'finish_date_2' => 'date',
        'ship_transfers_tt_date' => 'date',
        'prepared_date' => 'date',
        'verified_date' => 'date',
        'verified_at' => 'datetime',
        'is_urgent' => 'boolean',
        'stripping_notified' => 'boolean',
        'urgent_request' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(MwsStep::class, 'mws_part_id');
    }

    public function consumables()
    {
        return $this->hasMany(MwsConsumable::class, 'mws_part_id')->orderBy('order');
    }

    public function indockTask()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function getTotalDurationAttribute()
    {
        return $this->man_hours;
    }
}
