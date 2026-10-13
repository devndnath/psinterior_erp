<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes the "vendors have no way to submit their own invoice" audit
 * gap (the vendor portal's Ledger page has carried a documented "this is
 * NOT a true vendor invoicing system" disclaimer since Phase 11 —
 * PHASE11_SUMMARY.md). A vendor files one of these against their own
 * Purchase Order from the vendor portal; staff review/approve or reject
 * it from the staff side. Approval creates the matching payable Expense
 * row (VendorInvoiceService::approve()) so it shows up in the existing
 * vendor Ledger view, rather than inventing a second, parallel ledger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('vi_no')->unique();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            // The number printed on the vendor's own paper/PDF invoice —
            // free text, distinct from our internal vi_no, so staff can
            // match it against what the vendor is chasing payment for.
            $table->string('vendor_invoice_no')->nullable();
            $table->date('invoice_date');
            $table->decimal('amount', 14, 2);
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            // Set only on approval — nullOnDelete, not cascade, so
            // deleting the posted Expense later doesn't silently delete
            // the record of the vendor's invoice itself.
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index(['purchase_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_invoices');
    }
};
