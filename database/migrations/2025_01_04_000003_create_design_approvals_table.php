<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_revision_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->string('decision'); // App\Enums\ApprovalDecision
            $table->text('comment')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();

            $table->index('design_revision_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_approvals');
    }
};
