<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incoming_item_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_item_id')->constrained('incoming_items')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->string('barcode_number');
            $table->decimal('gross_weight', 8, 2);
            $table->decimal('net_weight', 8, 2);
            $table->decimal('storage_weight', 8, 2);
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->decimal('labor_cost', 10, 2);
            $table->date('expired_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_item_details');
    }
};
