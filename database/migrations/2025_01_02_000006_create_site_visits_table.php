<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sv_no')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            // project_id added in Phase 3 (projects table doesn't exist
            // yet) — a site visit can happen before a project is opened,
            // so this is never a hard requirement, only an enrichment
            // once Projects ships.
            $table->text('site_address')->nullable();
            $table->date('visit_date');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purpose')->nullable();
            $table->text('requirements')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['client_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
