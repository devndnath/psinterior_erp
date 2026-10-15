<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee-side reimbursement claims (conveyance to a site visit/client
 * meeting, meals, accommodation, etc.) — a distinct concept from the
 * `expenses` table, which records company/project spend paid to a
 * vendor. This is money the COMPANY owes the EMPLOYEE back.
 *
 * Self-submitted via the My Expense Claims self-service screen (any
 * staff member with a linked Employee record, no permission needed —
 * same reasoning as leaves self-service), reviewed by HR/Accounts
 * (`edit_expense_claims`), and — once approved — picked up by
 * PayrollService::computeDraft() for the employee's next payroll run.
 * `payroll_id` is set at that point so the same claim is never counted
 * into two different payroll runs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            // Optional context for the claim — which project/site visit it
            // relates to. Both nullable: a claim doesn't have to tie back
            // to a project (e.g. a client office meeting with no project
            // yet) or a logged site visit record.
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('site_visit_id')->nullable()->constrained('site_visits')->nullOnDelete();
            $table->string('type'); // App\Enums\ExpenseClaimType
            $table->date('date');
            $table->decimal('amount', 14, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // App\Enums\ExpenseClaimStatus
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            // Set once an Approved claim is pulled into a payroll run by
            // PayrollService::computeDraft()/generate() — excludes it from
            // being pulled into a later run for the same or any other
            // month.
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_claims');
    }
};
