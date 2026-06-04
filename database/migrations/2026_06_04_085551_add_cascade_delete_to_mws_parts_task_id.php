<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key lama (pakai nama exact dari pg_constraint)
        DB::statement('ALTER TABLE mws_parts DROP CONSTRAINT IF EXISTS mws_parts_task_id_foreign');

        // Tambah foreign key baru dengan CASCADE
        Schema::table('mws_parts', function (Blueprint $table) {
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE mws_parts DROP CONSTRAINT IF EXISTS mws_parts_task_id_foreign');

        Schema::table('mws_parts', function (Blueprint $table) {
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('restrict');
        });
    }
};