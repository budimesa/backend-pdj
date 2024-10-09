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
        Schema::create('customer_credit_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->decimal('limit_amount', 15, 2)->nullable(); // Dapat bernilai null jika is_unlimited true
            $table->decimal('limit_used', 15, 2)->default(0); // Field untuk limit yang telah terpakai
            $table->decimal('limit_remaining', 15, 2)->nullable(); // Field untuk limit yang tersisa
            $table->boolean('is_unlimited')->default(false); // Field untuk status unlimited
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Menambahkan foreign key constraint
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_credit_limits');
    }
};
