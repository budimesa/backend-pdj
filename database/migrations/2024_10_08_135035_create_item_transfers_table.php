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
        Schema::create('item_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code')->unique(); // Kode transfer
            $table->foreignId('from_warehouse_id')->constrained('warehouses'); // ID gudang asal
            $table->foreignId('to_warehouse_id')->constrained('warehouses'); // ID gudang tujuan
            $table->integer('total_quantity'); // Total jumlah barang
            $table->decimal('total_price', 15, 2); // Total harga barang
            $table->timestamps(); // Tanggal dibuat dan diperbarui
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transfers');
    }
};
