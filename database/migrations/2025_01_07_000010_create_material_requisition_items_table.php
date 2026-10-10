<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requisition_items', function (Blueprint $table) {
            $table->id();
            // Full `material_requisition_id` name, mirroring how
            // change_order_items names its FK `change_order_id` rather
            // than an abbreviation like `co_id`.
            $table->foreignId('material_requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 14, 2);
            $table->decimal('issued_quantity', 14, 2)->default(0);
            $table->timestamps();

            $table->index('material_requisition_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requisition_items');
    }
};
