<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Calls and follow-ups for clients, not only leads. `lead_followups` is
 * the one call log for the whole CRM: a row belongs to a lead, a client,
 * or both (a converted lead's history follows it to the client), so the
 * lead page, the client page, the Calls page, the CRM overview and the
 * calendar all read the same rows. `outcome` records how a finished call
 * went (App\Enums\CallOutcome).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->change();
            $table->foreignId('client_id')->nullable()->after('lead_id')->constrained()->cascadeOnDelete();
            $table->string('outcome')->nullable()->after('status');
            $table->index(['client_id', 'date']);
        });

        // A converted lead's calls belong to its client too.
        DB::table('leads')->whereNotNull('converted_client_id')->orderBy('id')
            ->each(fn ($lead) => DB::table('lead_followups')->where('lead_id', $lead->id)
                ->update(['client_id' => $lead->converted_client_id]));
    }

    public function down(): void
    {
        DB::table('lead_followups')->whereNull('lead_id')->delete();

        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'date']);
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn('outcome');
            $table->foreignId('lead_id')->nullable(false)->change();
        });
    }
};
