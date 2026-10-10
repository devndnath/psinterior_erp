<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receive_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receive_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained()->restrictOnDelete();
            // Snapshot of the PO item's ordered qty at receipt time, for
            // record-keeping only — never authoritative for totals (that
            // always comes from purchase_order_items.qty itself via
            // PurchaseOrderService::recalculateReceiptStatus()).
            $table->decimal('ordered_qty', 14, 2);
            $table->decimal('received_qty', 14, 2);
            $table->decimal('damaged_qty', 14, 2)->default(0);
            // Stored for query simplicity even though it's derivable
            // (received_qty - damaged_qty) — this is the figure that
            // actually reaches InventoryService::recordMovement().
            $table->decimal('accepted_qty', 14, 2);
            $table->timestamps();

            // Explicit short name — the auto-generated one
            // ("goods_receive_items_goods_receive_id_purchase_order_item_id_index",
            // 69 chars) exceeds MySQL's 64-char identifier limit (error 1059).
            $table->index(['goods_receive_id', 'purchase_order_item_id'], 'gri_grn_poi_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receive_items');
    }
};
