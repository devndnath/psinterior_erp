<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Closes a gap flagged in the Phase 7 known-issues note: a Material
 * Requisition could be issued but had no way to record material that
 * came back unused from site — consumedQuantity() on the model treats
 * "consumed" as issued_quantity - returned_quantity, so no separate
 * consumed column is needed; returned_quantity is the only new fact to
 * capture. Additive-only, matches the non-destructive-migration rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_requisition_items', function (Blueprint $table) {
            $table->decimal('returned_quantity', 14, 2)->default(0)->after('issued_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('material_requisition_items', function (Blueprint $table) {
            $table->dropColumn('returned_quantity');
        });
    }
};
