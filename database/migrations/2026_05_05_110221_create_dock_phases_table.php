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
        Schema::create('dock_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['predock', 'indock', 'postdock']);
            $table->string('no')->nullable();
            $table->string('name')->nullable();
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
        DB::statement('DROP TABLE IF EXISTS dock_phases CASCADE');
    }
};
