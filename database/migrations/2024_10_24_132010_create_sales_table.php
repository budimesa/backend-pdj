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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->integer('status');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->decimal('total_item_price', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->nullable();
            $table->decimal('other_fee', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
