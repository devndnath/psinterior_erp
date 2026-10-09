<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central, polymorphic document store (ARCHITECTURE.md §3.1, spec §15).
     * Every later module (clients, projects, designs, quotations, contracts,
     * invoices, purchase orders, employees, vendors, site visits) attaches
     * files here via `documentable_id`/`documentable_type` rather than
     * each owning its own upload table.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('disk')->default('uploads');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
