<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('stock_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->unsignedBigInteger('item_id');
            $table->string('item_code', 50)->nullable();
            $table->string('item_name', 255)->nullable();
            $table->string('item_uom', 50)->nullable();
            $table->decimal('qty_entered', 12, 4)->default(0);
            $table->string('uom_entered', 50);
            $table->decimal('qty_in_base_uom', 12, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stock_count_id', 'item_id'], 'unique_count_item');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('stock_count_lines');
    }
};
