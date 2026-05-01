<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('URGENT REQUEST BY', 50)->nullable()->index();
            $table->date('start_date')->nullable();
            $table->string('REF LOGISTIC PPC', 100)->nullable();
            $table->string('CUSTOMER', 100)->nullable();
            $table->string('WBS NO', 100)->nullable();
            $table->string('title', 255);
            $table->string('part_number', 100);
            $table->string('serial_number', 100);
            $table->string('job_type', 100)->nullable();
            $table->string('MDR DOC DEFECT', 100)->nullable();
            $table->string('REF', 100)->nullable();
            $table->string('AC TYPE', 100)->nullable();
            $table->string('iwo_no', 100)->unique();
            $table->string('SHOP AREA', 100)->nullable();
            $table->date('IWO DATE')->nullable();
            $table->string('WORKSHEET NO', 100)->nullable();
            $table->string('REMARK MWS', 350)->nullable();
            $table->string('TEST RESULT', 100)->nullable();
            $table->date('SCHEDULE DELIVERY ON TIME')->nullable();
            $table->integer('ECD FINISH WORKDAYS')->nullable();
            $table->integer('SELISIH WORK DAYS')->nullable();
            $table->string('PROSENTASE SCHEDULE', 100)->nullable();
            $table->date('WORKSHEET DATE')->nullable();
            $table->date('APPROVED DATE')->nullable();
            $table->string('FORM OUT NO', 100)->nullable();
            $table->string('TANDA TERIMA FO NO', 100)->nullable();
            $table->date('TANDA TERIMA FO DATE')->nullable();
            $table->date('STRIPPING REPORT DATE')->nullable();
            $table->date('STRIPPING ORDER BY SAP DATE')->nullable();
            $table->integer('SELISIH ORDER WORK DAYS')->nullable();
            $table->integer('TIME STRIPPING WORK DAYS')->nullable();
            $table->date('MAX STRIPPING DATE')->nullable();
            $table->string('TASE STRIPPING', 100)->nullable();
            $table->string('PROSENTASE BDP', 100)->nullable();
            $table->integer('QTY BDP')->nullable();
            $table->string('STATUS S US', 100)->nullable();
            $table->string('REVISION', 50)->nullable();
            $table->date('finish_date')->nullable();
            $table->date('FINISH DATE 2')->nullable();
            $table->string('MEN POWERS', 100)->nullable();
            $table->string('MAN HOURS', 50)->nullable();
            $table->string('DOCUMENT PENYERTA', 100)->nullable();
            $table->date('SHIP TRANSFER TT DATE')->nullable();
            $table->string('SHIP TRANSFER TT NO', 100)->nullable();
            $table->string('ISR NO', 100)->nullable();
            $table->integer('SELISIH SHIPPING WORK DAYS')->nullable();
            $table->string('TASE', 250)->nullable();
            $table->string('REMARK', 500)->nullable();
            $table->string('PREPARED BY', 100)->nullable();
            $table->date('PREPARED DATE')->nullable();
            $table->string('APPROVED BY', 100)->nullable();
            $table->string('VERIFIED BY', 100)->nullable();
            $table->date('VERIFIED DATE')->nullable();
            $table->boolean('STRIPPING NOTIFIED')->nullable();
            $table->string('CAPABILITY', 100)->nullable();
            $table->text('ATTACHMENT')->nullable();
            $table->string('status', 50)->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('URGENT REQUEST')->nullable();
            $table->integer('current_step')->nullable();
            $table->integer('SELISIH STRIPPING (WORK DAYS)')->nullable();
            $table->string('ZONE', 100)->nullable();
            $table->text('testcase')->nullable();
            $table->foreignId('indock_task_id')->nullable()->constrained('indock_tasks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mws_parts');
    }
};
