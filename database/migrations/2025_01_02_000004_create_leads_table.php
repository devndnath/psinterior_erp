<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lead_no')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('project_type')->nullable(); // App\Enums\ProjectType
            $table->decimal('estimated_budget', 14, 2)->nullable();
            $table->string('interested_service')->nullable();
            $table->foreignId('lead_source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority')->default('medium'); // App\Enums\LeadPriority
            $table->string('status')->default('new'); // App\Enums\LeadStatus
            $table->date('expected_closing_date')->nullable();
            $table->text('notes')->nullable();

            // Set once, on "Convert to Client" (spec §5) — never duplicated,
            // never reassigned to a different client afterwards.
            $table->foreignId('converted_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'assigned_to']);
            $table->index(['name', 'phone', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
