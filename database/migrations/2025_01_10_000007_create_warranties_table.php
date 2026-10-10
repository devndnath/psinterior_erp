<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            // Not unique — multiple warranties per project are allowed
            // (e.g. different terms for different scopes), per the ERD's
            // "projects 1-* warranties".
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
