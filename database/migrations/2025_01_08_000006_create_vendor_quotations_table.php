<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('received'); // App\Enums\VendorQuotationStatus
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['purchase_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_quotations');
    }
};
