<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->table('staff_users', function (Blueprint $table) {
            $table->boolean('restrict_today_report')->default(false)->after('api_token');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->table('staff_users', function (Blueprint $table) {
            $table->dropColumn('restrict_today_report');
        });
    }
};
