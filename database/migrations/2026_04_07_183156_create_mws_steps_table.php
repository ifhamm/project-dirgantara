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
        Schema::create('mws_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mws_part_id')->constrained('mws_parts');
            $table->integer('no');
            $table->string('description', 255);
            $table->text('details')->nullable();
            $table->string('planMan', 100)->nullable();
            $table->string('planHours', 100)->nullable();
            $table->text('man')->nullable();
            $table->string('hours', 50)->nullable();
            $table->string('tech', 100)->nullable();
            $table->string('insp', 100)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('completedBy', 100)->nullable();
            $table->string('completedDate', 100)->nullable();
            $table->string('timer_start_time', 50)->nullable();
            $table->text('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mws_steps');
    }
};
