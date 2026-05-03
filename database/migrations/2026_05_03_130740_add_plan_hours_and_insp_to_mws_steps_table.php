<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mws_steps', function (Blueprint $table) {
            // Kolom yang hilang (dari error log)
            $table->float('plan_hours')->nullable()->after('plan_man');
            
            // Kolom lain yang dibutuhkan (dari analisis sebelumnya)
            $table->string('insp')->nullable()->after('tech');
            $table->string('status_s_us')->nullable()->after('status');
            
            // Timer
            $table->timestamp('timer_start_time')->nullable()->after('completed_date');
        });
    }

    public function down(): void
    {
        Schema::table('mws_steps', function (Blueprint $table) {
            $table->dropColumn(['plan_hours', 'insp', 'status_s_us', 'timer_start_time']);
        });
    }
};