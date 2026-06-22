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
        // Tambah aircraft_series ke projects (jika belum ada)
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'aircraft_series')) {
                $table->string('aircraft_series')->nullable()->after('aircraft_type');
            }
        });

        // Tambah allocation_percentage ke dock_phases
        Schema::table('dock_phases', function (Blueprint $table) {
            if (!Schema::hasColumn('dock_phases', 'allocation_percentage')) {
                $table->decimal('allocation_percentage', 5, 2)->default(0)->after('allocation');
            }
        });

        // Tambah allocation_percentage ke task_groups
        Schema::table('task_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('task_groups', 'allocation_percentage')) {
                $table->decimal('allocation_percentage', 5, 2)->default(0)->after('allocation');
            }
        });

        // Tambah allocation_percentage ke tasks
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'allocation_percentage')) {
                $table->decimal('allocation_percentage', 5, 2)->default(0)->after('allocation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('aircraft_series');
        });

        Schema::table('dock_phases', function (Blueprint $table) {
            $table->dropColumn('allocation_percentage');
        });

        Schema::table('task_groups', function (Blueprint $table) {
            $table->dropColumn('allocation_percentage');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('allocation_percentage');
        });
    }
};
