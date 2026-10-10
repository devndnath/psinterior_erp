<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcontractor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontractor_id')->constrained('subcontractors')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->text('work_description')->nullable();
            $table->decimal('rate', 14, 2)->default(0);
            // Same column type `project_milestones.progress` uses
            // (unsignedTinyInteger, 0-100 percentage) — matched
            // deliberately for consistency across the codebase.
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();

            // 64-char identifier audit (per this phase's standing
            // instruction, given the gri_grn_poi_index hotfix in
            // 7328112): this table's own name is 25 chars, so every
            // default index/FK name here was computed by hand before
            // this migration was committed:
            //   - "subcontractor_assignments_subcontractor_id_foreign" = 50 chars (OK)
            //   - "subcontractor_assignments_project_id_foreign" = 45 chars (OK)
            //   - "subcontractor_assignments_project_id_subcontractor_id_index" = 61 chars (OK, under 64)
            // All three are within MySQL's limit, so no explicit short
            // name is required — but the composite index below is given
            // one anyway, to leave headroom if a column in this
            // composite is ever renamed to something longer.
            $table->index(['project_id', 'subcontractor_id'], 'sca_project_subcontractor_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractor_assignments');
    }
};
