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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_item_id')->constrained('incoming_items')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->string('description');
            $table->string('barcode_number');
            $table->decimal('gross_weight', 10, 2);
            $table->decimal('net_weight', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->integer('initial_stock');
            $table->integer('available_stock');
            $table->integer('actual_stock');
            $table->decimal('total_price', 15, 2);
            $table->decimal('labor_cost', 15, 2);
            $table->date('expiry_date')->nullable();
            $table->string('notes')->nullable();
            $table->tinyInteger('transaction_type')->unsigned()->default(1);
            $table->tinyInteger('price_status')->unsigned()->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
