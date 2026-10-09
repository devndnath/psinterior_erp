<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Optional — a contract is normally drawn up from an approved
            // Quotation (ContractService::createFromQuotation) but can
            // also be created standalone.
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->text('terms')->nullable();
            $table->string('status')->default('draft'); // App\Enums\ContractStatus
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
