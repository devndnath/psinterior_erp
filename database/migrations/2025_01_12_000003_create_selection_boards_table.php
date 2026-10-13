<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes the audit's single biggest domain-specific gap: until now there
 * was no structured way to record "client chose Option B tile for the
 * master bath" — just ad-hoc notes or emails. A board groups a set of
 * options (see create_selection_options_table) for one decision (one
 * tile, one paint color, one hardware finish, ...) on one project, and
 * carries the client's eventual choice once made.
 *
 * Deliberately simpler than the Design/DesignRevision/DesignApproval
 * trio: a selection is a one-shot pick ("which of these"), not an
 * iterative review-and-revise cycle, so there's no separate approvals
 * table here — the decision is recorded directly on this row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_boards', function (Blueprint $table) {
            $table->id();
            $table->string('board_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Freeform, not a `rooms` table — no such concept exists
            // anywhere else in this schema (BOQ, Design, Project all
            // scope by project only), so this stays a plain label like
            // "Master Bath" rather than introducing a new entity for it.
            $table->string('area')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // App\Enums\SelectionBoardStatus
            // Set together once a decision is recorded (SelectionBoardService::recordDecision).
            // Plain column here, not ->constrained() — selection_options
            // (which this points to) doesn't exist yet in this migration;
            // the actual FK constraint is added once that table exists,
            // in 2025_01_12_000005_add_decided_option_foreign_key_to_selection_boards_table.
            $table->unsignedBigInteger('decided_option_id')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->text('client_comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_boards');
    }
};
