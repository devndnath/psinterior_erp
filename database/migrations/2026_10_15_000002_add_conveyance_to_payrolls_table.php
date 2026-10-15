<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a dedicated `conveyance` line to payroll, kept separate from the
 * existing general-purpose `allowance` column: `allowance` stays a
 * manually-entered figure (e.g. housing/transport allowance set by HR),
 * while `conveyance` is the sum of this employee's Approved
 * ExpenseClaim rows for the month — auto-filled by
 * PayrollService::computeDraft() but still editable like every other
 * field before the payroll is saved. Included in net_salary alongside
 * the other additive components.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('conveyance', 14, 2)->default(0)->after('allowance');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('conveyance');
        });
    }
};
