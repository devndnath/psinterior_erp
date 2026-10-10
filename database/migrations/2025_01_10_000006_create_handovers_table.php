<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handovers', function (Blueprint $table) {
            $table->id();
            // Unique — one handover per project (ARCHITECTURE.md's ERD:
            // "projects 1-1 handovers").
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('handover_date');
            $table->text('notes')->nullable();
            // Set when the client's confirmation is recorded. No client
            // portal yet, so this is staff-recorded on the client's
            // behalf — same standing note as every prior phase's "no
            // client portal yet" caveat.
            $table->timestamp('client_confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handovers');
    }
};
