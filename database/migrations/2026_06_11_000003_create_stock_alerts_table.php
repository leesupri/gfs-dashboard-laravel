<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('item_name', 255)->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->enum('alert_type', ['low_stock', 'cost_ratio', 'sales_ratio'])->default('low_stock');
            $table->decimal('threshold_value', 10, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('staff_users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('stock_alerts');
    }
};
