<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('staff_users')->onDelete('cascade');
            $table->text('token');
            $table->enum('platform', ['android', 'ios']);
            $table->timestamps();

            $table->unique(['staff_user_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('device_tokens');
    }
};
