<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no')->unique();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // The receiving warehouse — GoodsReceiveService::create()
            // calls InventoryService::recordMovement() against this one.
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            // Set when this PO was created from a selected Vendor
            // Quotation (PurchaseOrderService::createFromVendorQuotation());
            // null for an ad-hoc PO with no PR/VQ upstream.
            $table->foreignId('purchase_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->date('expected_delivery')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('terms')->nullable();
            $table->string('status')->default('draft'); // App\Enums\PurchaseOrderStatus
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
