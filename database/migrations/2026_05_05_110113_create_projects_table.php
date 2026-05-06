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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('customer');
            $table->string('contract_no')->nullable();
            $table->string('aircraft_reg')->nullable();  // e.g. "811"
            $table->string('aircraft_type')->nullable(); // e.g. "CN235-110"
            $table->text('description')->nullable();
            $table->decimal('progress', 8, 6)->default(0);
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
        Schema::dropIfExists('projects');
    }
};
