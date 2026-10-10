<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            // Nullable — a meeting can be project-less (e.g. an internal
            // company meeting with no single project attached).
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('type'); // App\Enums\MeetingType
            $table->date('date');
            $table->string('time')->nullable();
            $table->string('location')->nullable();
            $table->text('agenda')->nullable();
            $table->text('discussion')->nullable();
            $table->text('decisions')->nullable();
            $table->text('follow_up')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
