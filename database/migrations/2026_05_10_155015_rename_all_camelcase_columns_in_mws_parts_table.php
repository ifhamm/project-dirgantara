<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $renames = [
        'UrgentRequestBy'            => 'urgent_request_by',
        'refLogisticPPC'             => 'ref_logistic_ppc',
        'wbsNO'                      => 'wbs_no',
        'mdrDocDeffect'              => 'mdr_doc_deffect',
        'acType'                     => 'ac_type',
        'shopArea'                   => 'shop_area',
        'iwoDate'                    => 'iwo_date',
        'wroksheetNo'                => 'worksheet_no',
        'remarkMWS'                  => 'remark_mws',
        'testResult'                 => 'test_result',
        'scheduleDeliveryOnTime'     => 'schedule_delivery_on_time',
        'ecdFinishWorkdays'          => 'ecd_finish_workdays',
        'selisihWorkDays'            => 'selisih_work_days',
        'presentasiSchedule'         => 'presentasi_schedule',
        'worksheetDate'              => 'worksheet_date',
        'approvedDate'               => 'approved_date',
        'formOutNo'                  => 'form_out_no',
        'tandaTerima_FO_NO'          => 'tanda_terima_fo_no',
        'tandaTerima_FO_DATE'        => 'tanda_terima_fo_date',
        'strippingReportDate'        => 'stripping_report_date',
        'strippingOrder_BY_SAP_DATE' => 'stripping_order_by_sap_date',
        'selisihOrderWorkDays'       => 'selisih_order_work_days',
        'timeStrippingWorkDays'      => 'time_stripping_work_days',
        'maxStrippingDate'           => 'max_stripping_date',
        'taseStripping'              => 'tase_stripping',
        'presentaseBDP'              => 'presentase_bdp',
        'qtyBDP'                     => 'qty_bdp',
        'STATUS_S_US'                => 'status_s_us_old',
        'menPowers'                  => 'men_powers',
        'manHours'                   => 'man_hours',
        'documentPenyerta'           => 'document_penyerta',
        'shipTransfers_TT_Date'      => 'ship_transfers_tt_date',
        'shipTransfers_TT_No'        => 'ship_transfers_tt_no',
        'isrNO'                      => 'isr_no',
        'selisihShippingWorkDays'    => 'selisih_shipping_work_days',
        'preparedBy'                 => 'prepared_by',
        'preparedDate'               => 'prepared_date',
        'approvedBy'                 => 'approved_by',
        'verified_By'                => 'verified_by_old',
        'verifiedDate'               => 'verified_date',
        'strippingNotified'          => 'stripping_notified',
        'isUrgent'                   => 'is_urgent',
        'urgentRequest'              => 'urgent_request',
        'selisihStrippingWorkDays'   => 'selisih_stripping_work_days',
        'verifiedBy'                 => 'verified_by',
        'verifiedAt'                 => 'verified_at',
        'status_s_us_'               => 'status_s_us',
    ];

    public function up(): void
    {
        foreach ($this->renames as $from => $to) {
            if (Schema::hasColumn('mws_parts', $from) && !Schema::hasColumn('mws_parts', $to)) {
                Schema::table('mws_parts', function (Blueprint $table) use ($from, $to) {
                    $table->renameColumn($from, $to);
                });
            }
        }

        // Drop duplikat verified_by_old jika verified_by sudah ada
        if (Schema::hasColumn('mws_parts', 'verified_by_old')) {
            DB::statement('UPDATE mws_parts SET verified_by = verified_by_old WHERE verified_by IS NULL AND verified_by_old IS NOT NULL');
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->dropColumn('verified_by_old');
            });
        }

        // Drop duplikat status_s_us_old jika status_s_us sudah ada
        if (Schema::hasColumn('mws_parts', 'status_s_us_old')) {
            DB::statement('UPDATE mws_parts SET status_s_us = status_s_us_old WHERE status_s_us IS NULL AND status_s_us_old IS NOT NULL');
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->dropColumn('status_s_us_old');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->renames) as $to => $from) {
            if (Schema::hasColumn('mws_parts', $from) && !Schema::hasColumn('mws_parts', $to)) {
                Schema::table('mws_parts', function (Blueprint $table) use ($from, $to) {
                    $table->renameColumn($from, $to);
                });
            }
        }
    }
};