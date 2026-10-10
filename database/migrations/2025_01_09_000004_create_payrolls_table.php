<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            // Stored as 'YYYY-MM' (e.g. '2026-10') rather than a date
            // column — a payroll run is for a calendar month, not a day,
            // and this keeps the uniqueness check and the <input
            // type="month"> binding on the Form both trivial string
            // comparisons.
            $table->string('month');
            $table->decimal('basic', 14, 2)->default(0);
            $table->decimal('allowance', 14, 2)->default(0);
            $table->decimal('overtime', 14, 2)->default(0);
            $table->decimal('bonus', 14, 2)->default(0);
            $table->decimal('deduction', 14, 2)->default(0);
            $table->decimal('advance', 14, 2)->default(0);
            // Computed by PayrollService::generate() as
            // basic+allowance+overtime+bonus-deduction-advance — stored,
            // not derived on read, so a Finalized/Paid payroll's figure
            // never silently changes if one of the components is read
            // differently later.
            $table->decimal('net_salary', 14, 2)->default(0);
            $table->string('status')->default('draft'); // App\Enums\PayrollStatus
            $table->timestamps();

            // Default name "payrolls_employee_id_month_unique" (33 chars)
            // — under the limit, left as-is.
            $table->unique(['employee_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
