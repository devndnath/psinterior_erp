<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('area')->nullable();
            $table->date('inspection_date');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('result'); // App\Enums\InspectionResult
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'inspection_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
