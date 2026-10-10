<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->string('checklist_item');
            // Each checklist line gets its own pass/fail/requires_correction,
            // independent of the inspection's overall result.
            $table->string('result'); // App\Enums\InspectionResult
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('inspection_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
