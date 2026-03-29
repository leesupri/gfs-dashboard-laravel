<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->foreignId('target_staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_audit_logs');
    }
};