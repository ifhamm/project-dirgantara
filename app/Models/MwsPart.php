<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MwsPart extends Model
{
    use HasFactory;

    protected $table = 'mws_parts';

    protected $fillable = [
        'part_id', 'customer_id', 'URGENT REQUEST BY', 'START DATE', 'REF LOGISTIC PPC', 'CUSTOMER', 'WBS NO',
        'TITTLE', 'PART NUMBER', 'SERIAL NUMBER', 'jobType', 'MDR DOC DEFECT', 'REF', 'AC TYPE', 'IWO NO',
        'SHOP AREA', 'IWO DATE', 'WORKSHEET NO', 'REMARK MWS', 'TEST RESULT', 'SCHEDULE DELIVERY ON TIME',
        'ECD FINISH WORKDAYS', 'SELISIH WORK DAYS', 'PROSENTASE SCHEDULE', 'WORKSHEET DATE', 'APPROVED DATE',
        'FORM OUT NO', 'TANDA TERIMA FO NO', 'TANDA TERIMA FO DATE', 'STRIPPING REPORT DATE',
        'STRIPPING ORDER BY SAP DATE', 'SELISIH ORDER WORK DAYS', 'TIME STRIPPING WORK DAYS', 'MAX STRIPPING DATE',
        'TASE STRIPPING', 'PROSENTASE BDP', 'QTY BDP', 'STATUS S US', 'REVISION', 'FINISH DATE', 'FINISH DATE 2',
        'MEN POWERS', 'MAN HOURS', 'DOCUMENT PENYERTA', 'SHIP TRANSFER TT DATE', 'SHIP TRANSFER TT NO', 'ISR NO',
        'SELISIH SHIPPING WORK DAYS', 'TASE', 'REMARK', 'PREPARED BY', 'PREPARED DATE', 'APPROVED BY',
        'VERIFIED BY', 'VERIFIED DATE', 'STRIPPING NOTIFIED', 'CAPABILITY', 'ATTACHMENT', 'STATUS', 'IS URGENT',
        'URGENT REQUEST', 'CURRENT STEP', 'SELISIH STRIPPING (WORK DAYS)', 'ZONE', 'testcase', 'indock_task_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function indockTask()
    {
        return $this->belongsTo(IndockTask::class);
    }

    public function steps()
    {
        return $this->hasMany(MwsStep::class);
    }

    public function strippings()
    {
        return $this->hasMany(Stripping::class);
    }
}
