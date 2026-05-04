<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_mws_features.php
    public function up()
    {
        // Tabel consumables (terpisah, relasi ke mws_part)
        Schema::create('mws_consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mws_part_id')->constrained('mws_parts')->onDelete('cascade');
            $table->string('name');
            $table->string('identification')->nullable(); // kolom Identification/References
            $table->string('quantity')->default('AR');    // AR = As Required
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS mws_consumables CASCADE');
    }
};
