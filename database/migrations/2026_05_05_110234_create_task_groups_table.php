<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dock_phase_id')->constrained()->cascadeOnDelete();
            $table->string('no')->nullable();       // B.1, B.2, dst
            $table->string('name');
            $table->decimal('progress', 8, 6)->default(0);
            $table->decimal('allocation', 8, 6)->default(0);
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->integer('work_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS task_groups CASCADE');
    }
};
