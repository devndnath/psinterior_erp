<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes the "no structured payment plan" audit gap: a contract's
 * installments ("30% advance on signing, 30% on design approval, ...")
 * previously lived only in the free-text `contracts.payment_terms`
 * field, with no way to compare what was planned against what's
 * actually been invoiced and collected. A line here is a sub-resource
 * of its Contract — same relationship as ContractItem, no standalone
 * index/number of its own (ContractPolicy's view/update abilities gate
 * it, not a new permission module).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedule_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            // Optional "due when this milestone completes" trigger instead
            // of (or alongside) a fixed due_date — ProjectMilestone has no
            // existing link to payments anywhere else in this schema, so
            // this is a net-new, fully optional relation.
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            // Set once staff raises/links the actual invoice for this
            // installment. nullOnDelete, not cascade — deleting the
            // invoice should un-plan the line, not delete the plan.
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('label');
            // Both nullable, but the service always ends up with `amount`
            // populated — either entered directly or computed from
            // `percentage` x the contract's amount AT THE TIME THE LINE
            // WAS ADDED. Deliberately a snapshot, not a live calculation:
            // `amount` never silently changes if the contract total is
            // edited later, which would otherwise retroactively rewrite
            // an installment plan the client may have already agreed to.
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('amount', 14, 2);
            $table->date('due_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedule_lines');
    }
};
