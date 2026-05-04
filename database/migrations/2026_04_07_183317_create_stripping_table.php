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
            $table->string('bdpName', 255)->nullable();
            $table->string('bdpNumber', 100)->nullable();
            $table->string('bdpNumberEqv', 100)->nullable();
            $table->integer('qty')->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('opNumber', 100)->nullable();
            $table->date('opDate')->nullable();
            $table->string('defect', 255)->nullable();
            $table->string('mtNumber', 100)->nullable();
            $table->integer('mtQty')->nullable();
            $table->date('mtDate')->nullable();
            $table->foreignId('mws_part_id')->constrained('mws_parts')->onDelete('cascade');
            $table->string('remark', 100)->nullable();
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
