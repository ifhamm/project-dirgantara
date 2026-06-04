<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus kolom task_id dan foreign key-nya jika masih ada
        if (Schema::hasColumn('mws_parts', 'task_id')) {
            Schema::table('mws_parts', function (Blueprint $table) {
                // Drop foreign key dulu (nama constraint biasanya auto-generated)
                $table->dropForeign(['task_id']);
                $table->dropColumn('task_id');
            });
        }

        // 2. Pastikan task_id ada (kalau belum)
        if (!Schema::hasColumn('mws_parts', 'task_id')) {
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->unsignedBigInteger('task_id')->nullable();
            });
        }

        // 3. Cek apakah foreign key task_id sudah ada
        $fkExists = DB::select("
            SELECT conname 
            FROM pg_constraint 
            JOIN pg_class ON pg_class.oid = pg_constraint.conrelid 
            WHERE pg_class.relname = 'mws_parts' 
            AND pg_constraint.conname LIKE '%task_id%'
            LIMIT 1
        ");

        // 4. Kalau belum ada, tambahkan foreign key
        if (empty($fkExists)) {
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->foreign('task_id')
                      ->references('id')
                      ->on('tasks')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('mws_parts', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn('task_id');
            
            $table->foreignId('indock_task_id')
                  ->constrained('indock_tasks');
        });
    }
};