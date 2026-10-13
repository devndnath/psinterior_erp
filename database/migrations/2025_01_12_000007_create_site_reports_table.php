<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily Site Report / Site Diary — closes the "no daily progress log"
 * audit gap. Unlike SiteVisit (a one-off scoping visit, sv_no-numbered,
 * client-scoped) this is a recurring, project-scoped daily entry filed by
 * whoever is on site, so it's always tied to a project (not nullable) and
 * carries no document number of its own — same "no own number" precedent
 * as Meeting. A full CRUD module (own Policy + permission set), not a
 * tight Contract-style sub-resource, because it's created/edited by field
 * staff independently of any other aggregate and needs its own
 * view/create/edit/delete permission set for site-engineer/supervisor
 * roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Who filed the report — nullable so a report isn't lost if
            // the filing user's account is later removed.
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('report_date');
            $table->string('weather')->nullable();
            $table->unsignedInteger('manpower_count')->nullable();
            $table->text('work_summary');
            $table->text('materials_notes')->nullable();
            $table->text('issues')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_reports');
    }
};
