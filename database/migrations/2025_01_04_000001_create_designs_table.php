<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->string('design_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->nullable(); // App\Enums\DesignCategory
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); // App\Enums\DesignStatus — staff-facing stage, kept in sync by DesignService
            $table->string('client_approval_status')->nullable(); // App\Enums\ApprovalDecision — latest client decision, null until one is recorded
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
