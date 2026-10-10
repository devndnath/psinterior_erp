<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
            // ARCHITECTURE.md's full ERD has a `vendor_id` FK to a `vendors`
            // table, but Vendors is a Phase 8 module that doesn't exist
            // yet — a plain nullable name string stands in for now. A
            // follow-up migration will add `vendor_id`, backfill it from
            // this column by name match, and drop this column once Phase 8
            // ships.
            $table->string('vendor_name')->nullable();
            $table->decimal('amount', 14, 2);
            $table->date('date');
            $table->string('method')->default('cash'); // App\Enums\PaymentMethod (reused from Payments)
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['project_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
