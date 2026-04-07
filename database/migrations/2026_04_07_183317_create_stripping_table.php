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
        Schema::create('stripping', function (Blueprint $table) {
            $table->id();
            $table->string('BDP NAME', 255)->nullable();
            $table->string('BDP NUMBER', 100)->nullable();
            $table->string('BDP_NUMBER_Eqv', 100)->nullable();
            $table->integer('QTY')->nullable();
            $table->string('UNIT', 50)->nullable();
            $table->string('OP NUMBER', 100)->nullable();
            $table->date('OP DATE')->nullable();
            $table->string('DEFECT', 255)->nullable();
            $table->string('MT NUMBER', 100)->nullable();
            $table->integer('MT QTY')->nullable();
            $table->date('MT DATE')->nullable();
            $table->foreignId('mws_part_id')->constrained('mws_parts');
            $table->string('REMARK', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripping');
    }
};
