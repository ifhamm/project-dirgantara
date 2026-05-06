<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_group_id')->constrained()->cascadeOnDelete();
            $table->string('no')->nullable();       // nomor urut dari excel
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
        Schema::dropIfExists('tasks');
    }
};
