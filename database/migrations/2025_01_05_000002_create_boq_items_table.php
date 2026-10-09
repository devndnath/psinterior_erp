<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_id')->constrained()->cascadeOnDelete();
            $table->string('item');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('qty', 12, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('rate', 14, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('labour_cost', 14, 2)->default(0);
            $table->decimal('material_cost', 14, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('boq_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_items');
    }
};
