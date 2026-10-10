<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('qty', 14, 2);
            $table->decimal('rate', 14, 2);
            $table->decimal('amount', 14, 2);
            $table->timestamps();

            $table->index('vendor_quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_quotation_items');
    }
};
