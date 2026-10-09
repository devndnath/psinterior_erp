<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->foreignId('file_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->text('client_feedback')->nullable();
            // Append-only (ARCHITECTURE.md §3.5 / spec §13): a new revision
            // is always a new row, never an update to this one, except to
            // move it through its own status/feedback lifecycle.
            $table->string('status')->default('draft'); // App\Enums\DesignRevisionStatus
            $table->timestamps();

            $table->unique(['design_id', 'version']);
            $table->index('design_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_revisions');
    }
};
