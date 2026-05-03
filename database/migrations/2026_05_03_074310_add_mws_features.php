<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        // Sub-steps di dalam mws_steps
        Schema::create('mws_sub_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mws_step_id')->constrained('mws_steps')->onDelete('cascade');
            $table->string('label');        // "a", "b", "c", dll — di-generate otomatis
            $table->text('description');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Kolom caution di mws_steps
        Schema::table('mws_steps', function (Blueprint $table) {
            $table->text('caution')->nullable()->after('description');
            // "note" opsional juga jika ingin pisah dari details
            $table->text('note')->nullable()->after('caution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
