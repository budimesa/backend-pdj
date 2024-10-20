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
        Schema::create('incoming_items', function (Blueprint $table) {
            $table->id();
            $table->string('incoming_item_code')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->date('shipment_date');
            $table->date('received_date');
            $table->decimal('total_item_price', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->nullable();
            $table->decimal('labor_cost', 15, 2)->nullable();
            $table->decimal('other_fee', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2);
            $table->text('notes')->nullable();
            $table->text('invoice_files')->nullable();
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
        Schema::dropIfExists('incoming_items');
    }
};
