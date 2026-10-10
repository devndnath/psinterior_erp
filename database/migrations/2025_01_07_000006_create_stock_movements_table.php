<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The append-only ledger InventoryService writes alongside every
        // warehouse_stocks update. `quantity` is signed: positive = stock
        // increase, negative = stock decrease. `reference_type`/
        // `reference_id` point at whatever caused the movement (a
        // StockTransfer, a MaterialRequisition) or are null for a manual
        // opening/adjustment entry.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('type'); // App\Enums\StockMovementType
            $table->decimal('quantity', 14, 2);
            $table->nullableMorphs('reference');
            // The resulting warehouse_stocks.quantity after this entry,
            // captured at write time for a fast audit trail without
            // having to replay the whole ledger.
            $table->decimal('balance_after', 14, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
