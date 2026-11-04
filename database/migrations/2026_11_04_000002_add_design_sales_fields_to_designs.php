<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A design can be sold on its own, without a project. Such a "sold
 * design" carries its client directly (`client_id`) and every design can
 * carry a `price`. The Design Sale invoice raised for it points back at
 * the design (`invoices.design_id`) so the list can show what's been
 * billed and paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->change();
            $table->foreignId('client_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            $table->decimal('price', 14, 2)->nullable()->after('category');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('design_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('design_id');
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn('price');
            // Only reversible while every design still has a project.
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};
