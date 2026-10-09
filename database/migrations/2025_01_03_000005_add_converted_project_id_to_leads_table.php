<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deliberately deferred in Phase 2's leads migration (the projects table
 * didn't exist yet) — see that migration's comment. A lead is converted
 * to a client first (Phase 2), then a project is opened for that client;
 * when that project is created from the lead, ProjectService stamps this
 * column so the lead's full conversion trail is visible without joining
 * through clients.leads().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('converted_project_id')->nullable()->after('converted_client_id')
                ->constrained('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_project_id');
        });
    }
};
