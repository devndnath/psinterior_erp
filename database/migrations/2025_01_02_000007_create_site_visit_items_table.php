<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per room/area measured on a visit (spec §7 — "Living Room:
     * Length/Width/Height/Area", "Bedroom: ..."). `area` is stored, not
     * purely computed on the fly, because SiteVisitService always writes
     * it alongside length/width at save time (spec: "automatically
     * calculate area where applicable") and PDF/report generation reads
     * it directly without re-deriving.
     */
    public function up(): void
    {
        Schema::create('site_visit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('area_name'); // e.g. "Living Room", "Master Bedroom"
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('area', 10, 2)->nullable(); // length * width, auto-calculated
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('site_visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visit_items');
    }
};
