<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snags', function (Blueprint $table) {
            $table->id();
            $table->string('snag_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Nullable — a snag can originate from a formal inspection or
            // be raised ad-hoc.
            $table->foreignId('inspection_id')->nullable()->constrained('inspections')->nullOnDelete();
            $table->string('area')->nullable();
            $table->text('issue');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('deadline')->nullable();
            $table->string('status')->default('pending'); // App\Enums\SnagStatus
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snags');
    }
};
