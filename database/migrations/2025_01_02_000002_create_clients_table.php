<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_no')->unique();
            $table->string('name');
            $table->string('type')->default('individual'); // App\Enums\ClientType
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('site_address')->nullable();
            $table->string('tax_id')->nullable(); // NID / Trade License
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            // Links a client to a portal login — nullable until the client
            // portal is built (Phase 11); the guard is already wired
            // (ARCHITECTURE.md §5) so this column has somewhere to point
            // the moment a login is issued.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name', 'phone', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
