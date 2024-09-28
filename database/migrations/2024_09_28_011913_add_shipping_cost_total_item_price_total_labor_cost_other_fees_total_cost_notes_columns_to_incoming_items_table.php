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
        Schema::table('incoming_items', function (Blueprint $table) {
            $table->decimal('total_item_price', 10, 2)->after('received_date');
            $table->decimal('shipping_cost', 10, 2)->nullable()->after('total_item_price');
            $table->decimal('labor_cost', 10, 2)->nullable()->after('shipping_cost');
            $table->decimal('other_fee', 10, 2)->nullable()->after('labor_cost');
            $table->decimal('total_cost', 10, 2)->after('other_fee');
            $table->text('notes')->nullable()->after('total_cost');
            $table->text('invoice_files')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_items', function (Blueprint $table) {
            //
        });
    }
};
