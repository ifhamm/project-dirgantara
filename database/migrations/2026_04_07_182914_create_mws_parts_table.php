<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mws_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_id', 50)->unique();
            // store customer name directly (no foreign key to customers table)
            $table->string('customer_name', 100)->nullable();
            $table->string('UrgentRequestBy', 50)->nullable()->index();
            $table->date('start_date')->nullable();
            $table->string('refLogisticPPC', 100)->nullable();
            $table->string('wbsNO', 100)->nullable();
            $table->string('title', 255);
            $table->string('part_number', 100);
            $table->string('serial_number', 100);
            $table->string('job_type', 100)->nullable();
            $table->string('mdrDocDeffect', 100)->nullable();
            $table->string('ref', 100)->nullable();
            $table->string('acType', 100)->nullable();
            $table->string('iwo_no', 100)->unique();
            $table->string('shopArea', 100)->nullable();
            $table->date('iwoDate')->nullable();
            $table->string('wroksheetNo', 100)->nullable();
            $table->string('remarkMWS', 350)->nullable();
            $table->string('testResult', 100)->nullable();
            $table->date('scheduleDeliveryOnTime')->nullable();
            $table->integer('ecdFinishWorkdays')->nullable();
            $table->integer('selisihWorkDays')->nullable();
            $table->string('presentasiSchedule', 100)->nullable();
            $table->date('worksheetDate')->nullable();
            $table->date('approvedDate')->nullable();
            $table->string('formOutNo', 100)->nullable();
            $table->string('tandaTerima_FO_NO', 100)->nullable();
            $table->date('tandaTerima_FO_DATE')->nullable();
            $table->date('strippingReportDate')->nullable();
            $table->date('strippingOrder_BY_SAP_DATE')->nullable();
            $table->integer('selisihOrderWorkDays')->nullable();
            $table->integer('timeStrippingWorkDays')->nullable();
            $table->date('maxStrippingDate')->nullable();
            $table->string('taseStripping', 100)->nullable();
            $table->string('presentaseBDP', 100)->nullable();
            $table->integer('qtyBDP')->nullable();
            $table->string('STATUS_S_US', 100)->nullable();
            $table->string('revision', 50)->nullable();
            $table->date('finish_date')->nullable();
            $table->date('finish_date_2')->nullable();
            $table->string('menPowers', 100)->nullable();
            $table->string('manHours', 50)->nullable();
            $table->string('documentPenyerta', 100)->nullable();
            $table->date('shipTransfers_TT_Date')->nullable();
            $table->string('shipTransfers_TT_No', 100)->nullable();
            $table->string('isrNO', 100)->nullable();
            $table->integer('selisihShippingWorkDays')->nullable();
            $table->string('tase', 250)->nullable();
            $table->string('remark', 500)->nullable();
            $table->string('preparedBy', 100)->nullable();
            $table->date('preparedDate')->nullable();
            $table->string('approvedBy', 100)->nullable();
            $table->string('verified_By', 100)->nullable();
            $table->date('verifiedDate')->nullable();
            $table->boolean('strippingNotified')->nullable();
            $table->string('capability', 100)->nullable();
            $table->text('attachment')->nullable();
            $table->string('status', 50)->nullable();
            $table->boolean('isUrgent')->default(false);
            $table->boolean('urgentRequest')->nullable();
            $table->integer('current_step')->nullable();
            $table->integer('selisihStrippingWorkDays')->nullable();
            $table->string('zone', 100)->nullable();
            $table->text('testcase')->nullable();
            $table->foreignId('indock_task_id')->nullable()->constrained('indock_tasks')->onDelete('cascade');
            $table->timestamps();
            $table->string('verifiedBy')->nullable();
            $table->timestamp('verifiedAt')->nullable();
            $table->string('status_s_us_')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS mws_parts CASCADE');
    }
};
