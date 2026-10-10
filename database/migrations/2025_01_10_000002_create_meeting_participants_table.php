<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            // Polymorphic target (App\Models\User / Client / Vendor) — a
            // small pivot-style model of its own (App\Models\MeetingParticipant),
            // not a reuse of the Document/documentable morph pattern.
            $table->unsignedBigInteger('participant_id');
            $table->string('participant_type');
            $table->timestamps();

            // Default name is 58 chars
            // (meeting_participants_participant_type_participant_id_index)
            // — under MySQL's 64-char limit, verified by hand per this
            // phase's identifier-length audit.
            $table->index(['participant_type', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
    }
};
