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
        Schema::create('indock_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('indock_projects')->onDelete('cascade');
            $table->string('name', 255)->nullable();
            $table->string('code', 50)->nullable();
            $table->integer('level')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('indock_tasks')->onDelete('cascade');
            $table->date('plannedstart')->nullable();
            $table->date('plannedEnd')->nullable();
            $table->float('progress')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indock_tasks');
    }
};
