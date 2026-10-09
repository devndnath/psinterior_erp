<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project_code')->unique();
            $table->string('name');
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            // Nullable — a project can be opened directly for an existing
            // client without ever having gone through a lead.
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('type')->nullable(); // App\Enums\ProjectType
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('contract_value', 14, 2)->nullable();
            $table->decimal('estimated_budget', 14, 2)->nullable();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('designer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('site_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority')->default('medium'); // App\Enums\LeadPriority (shared)
            $table->string('status')->default('planning'); // App\Enums\ProjectStatus
            $table->unsignedTinyInteger('progress')->default(0); // 0-100, auto-averaged from milestones once any exist
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
