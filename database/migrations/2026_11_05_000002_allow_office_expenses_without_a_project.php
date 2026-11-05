<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Office (head-office) expenses — rent, utilities, office supplies —
 * belong to no project, so `expenses.project_id` becomes optional. An
 * expense without a project is an office expense (the Office and
 * Head-wise Office Expense reports).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Only reversible while every expense still has a project.
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};
