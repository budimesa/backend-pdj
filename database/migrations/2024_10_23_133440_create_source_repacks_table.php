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
        Schema::create('source_repacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repack_id')->constrained('repacks')->onDelete('cascade');
            $table->foreignId('inventory_d_id')->constrained('inventory_details')->onDelete('cascade');
            $table->integer('quantity');
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
        Schema::dropIfExists('source_repacks');
    }
};
