<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('mr_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            // Deliberate addition beyond ARCHITECTURE.md's bare column
            // list (a reasonable-assumption addition per spec §81): the
            // table list didn't specify where issued stock comes from, so
            // this nullable FK lets the source warehouse be chosen either
            // at creation or later, at issue time
            // (MaterialRequisitionService::issue()).
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->date('date');
            $table->text('purpose')->nullable();
            $table->string('status')->default('pending'); // App\Enums\MaterialRequisitionStatus
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requisitions');
    }
};
