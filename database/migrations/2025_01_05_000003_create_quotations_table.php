<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_no')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Optional — a quotation can be built from scratch without a
            // BOQ, or copy its line items in from one (QuotationService).
            $table->foreignId('boq_id')->nullable()->constrained('boqs')->nullOnDelete();
            // Bumped (never decremented) whenever the quotation is revised
            // after leaving Draft; the pre-revision state is snapshotted
            // into quotation_versions first (spec §78 rule 1).
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft'); // App\Enums\QuotationStatus
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
