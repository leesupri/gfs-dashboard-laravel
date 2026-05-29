<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_page_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_user_id')->nullable()->index();
            $table->string('staff_name', 120);
            $table->string('route_name', 120)->nullable()->index();
            $table->string('url', 500);
            $table->string('method', 10)->default('GET');
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('staff_user_id')->references('id')->on('staff_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_page_logs');
    }
};
