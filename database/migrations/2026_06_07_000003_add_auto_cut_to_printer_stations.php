<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printer_stations', function (Blueprint $table) {
            $table->boolean('is_auto_cut')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('printer_stations', function (Blueprint $table) {
            $table->dropColumn('is_auto_cut');
        });
    }
};
