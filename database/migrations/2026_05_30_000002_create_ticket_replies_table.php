<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            // null = reply from the original submitter (non-staff)
            $table->foreignId('staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->string('reply_by_name', 100);   // display name
            $table->text('message');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
    }
};
