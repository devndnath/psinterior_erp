<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes the Section-B "drawing register" gap: a revision previously had
 * only a bare version number, no drawing number or issue date, and
 * nothing marked an older revision as superseded — a real on-site risk
 * (someone building off an outdated elevation). "Superseded" itself needs
 * no new column: DesignRevision::isSuperseded() derives it from whether a
 * later version exists on the same design, so it can never drift out of
 * sync with the append-only revision history. Additive-only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_revisions', function (Blueprint $table) {
            $table->string('drawing_no')->nullable()->after('version');
            $table->date('issue_date')->nullable()->after('drawing_no');
        });
    }

    public function down(): void
    {
        Schema::table('design_revisions', function (Blueprint $table) {
            $table->dropColumn(['drawing_no', 'issue_date']);
        });
    }
};
