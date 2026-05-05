<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MwsPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'UrgentRequestBy',
        'start_date',
        'refLogisticPPC',
        'customer_name',
        'wbsNO',
        'title',
        'part_number',
        'serial_number',
        'job_type',
        'mdrDocDeffect',
        'ref',
        'acType',
        'iwo_no',
        'shopArea',
        'iwoDate',
        'wroksheetNo',
        'remarkMWS',
        'testResult',
        'scheduleDeliveryOnTime',
        'ecdFinishWorkdays',
        'selisihWorkDays',
        'presentasiSchedule',
        'worksheetDate',
        'approvedDate',
        'formOutNo',
        'tandaTerima_FO_NO',
        'tandaTerima_FO_DATE',
        'strippingReportDate',
        'strippingOrder_BY_SAP_DATE',
        'selisihOrderWorkDays',
        'timeStrippingWorkDays',
        'maxStrippingDate',
        'taseStripping',
        'presentaseBDP',
        'qtyBDP',
        'STATUS_S_US',
        'revision',
        'finish_date',
        'finish_date_2',
        'menPowers',
        'manHours',
        'documentPenyerta',
        'shipTransfers_TT_Date',
        'shipTransfers_TT_No',
        'isrNO',
        'selisihShippingWorkDays',
        'tase',
        'remark',
        'preparedBy',
        'preparedDate',
        'approvedBy',
        'verified_By',
        'verifiedDate',
        'strippingNotified',
        'capability',
        'attachment',
        'status',
        'isUrgent',
        'urgentRequest',
        'current_step',
        'selisihStrippingWorkDays',
        'zone',
        'testcase',
        'indock_task_id',
        'verifiedBy',
        'verifiedAt',
        'status_s_us'
    ];

    protected $casts = [
        'start_date' => 'date',
        'iwoDate' => 'date',
        'scheduleDeliveryOnTime' => 'date',
        'worksheetDate' => 'date',
        'approvedDate' => 'date',
        'tandaTerima_FO_DATE' => 'date',
        'strippingReportDate' => 'date',
        'strippingOrder_BY_SAP_DATE' => 'date',
        'maxStrippingDate' => 'date',
        'finish_date' => 'date',
        'finish_date_2' => 'date',
        'shipTransfers_TT_Date' => 'date',
        'preparedDate' => 'date',
        'verifiedDate' => 'date',
        'verifiedAt' => 'datetime',
        'isUrgent' => 'boolean',
        'strippingNotified' => 'boolean',
        'urgentRequest' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(MwsStep::class, 'mws_part_id');
    }


    public function consumables()
    {
        return $this->hasMany(MwsConsumable::class, 'mws_part_id')->orderBy('order');
    }
}
