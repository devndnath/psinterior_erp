<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->string('brand')->nullable();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('purchase_price', 14, 2)->default(0);
            // Typical consumption rate — a planning aid (e.g. for BOQ/MR
            // estimation), not used in any stock calculation itself.
            $table->decimal('usage_rate', 12, 2)->nullable();
            // Reorder threshold: a product is "low stock" once its total
            // quantity across all warehouses drops below this value
            // (Product::isLowStock()).
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
