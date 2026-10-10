<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('sr_no')->unique();
            $table->foreignId('warranty_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients');
            $table->text('complaint');
            $table->foreignId('assigned_technician')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open'); // App\Enums\ServiceRequestStatus
            $table->timestamps();

            $table->index(['warranty_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
