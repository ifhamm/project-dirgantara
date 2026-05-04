<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mws_sub_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mws_step_id')->constrained('mws_steps')->onDelete('cascade');
            $table->string('label');        // "a", "b", "c", dll — di-generate otomatis
            $table->text('description');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS mws_sub_steps CASCADE');
    }
};
