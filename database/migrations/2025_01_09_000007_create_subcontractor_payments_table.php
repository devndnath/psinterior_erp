<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcontractor_payments', function (Blueprint $table) {
            $table->id();

            // 64-char identifier audit: this table's name is 23 chars
            // and the FK column name is long
            // ("subcontractor_assignment_id"), so the default
            // constraint name was computed by hand before committing:
            //   "subcontractor_payments_subcontractor_assignment_id_foreign"
            //   = 61 chars — under MySQL's 64-char limit, so the default
            // name is left as-is (no explicit short name required; see
            // the migration comment on subcontractor_assignments for the
            // sibling check on that table).
            $table->foreignId('subcontractor_assignment_id')
                ->constrained('subcontractor_assignments')
                ->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 14, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractor_payments');
    }
};
