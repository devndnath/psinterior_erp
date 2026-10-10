<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('main'); // App\Enums\WarehouseType
            $table->string('location')->nullable();
            // Not explicitly called out as a soft-delete table in
            // ARCHITECTURE.md §3.8, but soft-deleted for consistency with
            // every other master table in this codebase (general
            // assumption #3).
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
