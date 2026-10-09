<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Nullable — a task can belong directly to the project with no
            // milestone grouping (ARCHITECTURE.md §3.3).
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('priority')->default('medium'); // App\Enums\LeadPriority (shared)
            $table->string('status')->default('todo'); // App\Enums\TaskStatus
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
