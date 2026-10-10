<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fulfils the follow-up noted on `expenses.vendor_name` in Phase 6's
 * migration: now that `vendors` exists, add the real FK. `vendor_name`
 * is deliberately kept as a legacy/fallback free-text field rather than
 * dropped or backfilled — a blind backfill-by-name-match migration can't
 * be verified against real data in this sandbox, so it's left as a
 * follow-up for whoever runs this migration against production data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('vendor_name')->constrained('vendors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
        });
    }
};
