<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deliberately deferred in Phase 2's site_visits migration — see that
 * migration's comment. A site visit can happen before a project exists
 * (pre-sales measurement) so this stays nullable/optional even now that
 * Projects has shipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('client_id')
                ->constrained('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
