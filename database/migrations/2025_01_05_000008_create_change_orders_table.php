<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_orders', function (Blueprint $table) {
            $table->id();
            $table->string('co_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            // Net additional cost this change order adds to the project —
            // added to projects.contract_value when approved
            // (ChangeOrderService::recordClientDecision, ARCHITECTURE.md
            // §3.6 / spec §19). Can be negative for a scope reduction.
            $table->decimal('cost', 14, 2)->default(0);
            // App\Enums\ApprovalDecision — reused from the Design module
            // the same way Design keeps `status` and
            // `client_approval_status` as two related-but-independent
            // fields synced by the service layer.
            $table->string('client_approval_status')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('status')->default('pending'); // App\Enums\ChangeOrderStatus
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_orders');
    }
};
