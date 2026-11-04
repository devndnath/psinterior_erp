<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reference — who introduced a lead / client to the firm: an existing
 * client, one of the staff (an employee), or someone else (name +
 * phone). Recorded on the lead and carried onto the client when the lead
 * converts, so the References page can show every referrer with the
 * clients and business they brought.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['leads', 'clients'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('reference_type')->nullable()->index(); // client | employee | other
                $t->foreignId('reference_client_id')->nullable()->constrained('clients')->nullOnDelete();
                $t->foreignId('reference_employee_id')->nullable()->constrained('employees')->nullOnDelete();
                $t->string('reference_name')->nullable();
                $t->string('reference_phone', 50)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['leads', 'clients'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('reference_client_id');
                $t->dropConstrainedForeignId('reference_employee_id');
                $t->dropColumn(['reference_type', 'reference_name', 'reference_phone']);
            });
        }
    }
};
