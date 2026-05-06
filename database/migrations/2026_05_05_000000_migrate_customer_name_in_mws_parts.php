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
        if (!Schema::hasColumn('mws_parts', 'customer_name')) {
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->string('customer_name', 100)->nullable()->after('refLogisticPPC');
            });
        }

        if (Schema::hasColumn('mws_parts', 'customer')) {
            DB::table('mws_parts')->whereNotNull('customer')->update(['customer_name' => DB::raw('customer')]);
        }

        if (Schema::hasColumn('mws_parts', 'customer_id')) {
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }

        if (Schema::hasColumn('mws_parts', 'customer')) {
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->dropColumn('customer');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('mws_parts', 'customer')) {
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->string('customer', 100)->nullable()->after('refLogisticPPC');
            });
        }

        if (!Schema::hasColumn('mws_parts', 'customer_id')) {
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade')->after('part_id');
            });
        }

        if (Schema::hasColumn('mws_parts', 'customer_name')) {
            DB::table('mws_parts')->whereNotNull('customer_name')->update(['customer' => DB::raw('customer_name')]);
            Schema::table('mws_parts', function (Blueprint $table) {
                $table->dropColumn('customer_name');
            });
        }
    }
};
