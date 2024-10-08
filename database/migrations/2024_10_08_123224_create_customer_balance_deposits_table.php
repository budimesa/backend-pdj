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
        Schema::create('customer_balance_deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_balance_id');
            $table->decimal('deposit_amount', 15, 2);
            $table->timestamp('deposit_date');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Menambahkan foreign key constraint
            $table->foreign('customer_balance_id')->references('id')->on('customer_balances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_balance_deposits');
    }
};
