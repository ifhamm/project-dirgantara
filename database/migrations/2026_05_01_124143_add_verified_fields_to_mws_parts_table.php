<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mws_parts', function (Blueprint $table) {
            $table->string('verifiedBy')->nullable()->after('approvedAt');
            $table->timestamp('verifiedAt')->nullable()->after('verifiedBy');
            $table->string('status_s_us')->nullable()->after('verifiedAt');
        });
    }

    public function down(): void
    {
        Schema::table('mws_parts', function (Blueprint $table) {
            $table->dropColumn(['verifiedBy', 'verifiedAt', 'status_s_us']);
        });
    }
};