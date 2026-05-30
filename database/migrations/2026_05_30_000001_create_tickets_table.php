<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique()->index();

            // Who submitted (nullable = submitted from public/login form)
            $table->foreignId('staff_user_id')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->string('submitter_name', 100);
            $table->string('submitter_email', 150);

            $table->string('subject', 255);
            $table->text('description');

            $table->enum('category', [
                'general', 'technical', 'access', 'report', 'feature', 'other',
            ])->default('general');

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');

            // Staff assigned to handle this ticket
            $table->foreignId('assigned_to')->nullable()->constrained('staff_users')->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
