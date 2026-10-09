<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boqs', function (Blueprint $table) {
            $table->id();
            $table->string('boq_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Starts at 1 per project; a new version supersedes the
            // previous active one rather than overwriting it
            // (BoqService::createVersion, ARCHITECTURE.md §3.6).
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft'); // App\Enums\BoqStatus
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boqs');
    }
};
