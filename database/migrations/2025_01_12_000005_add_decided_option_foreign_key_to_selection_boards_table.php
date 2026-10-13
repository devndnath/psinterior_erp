<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Completes the circular reference between selection_boards and
 * selection_options: 2025_01_12_000003 created `decided_option_id` as a
 * plain unconstrained column because selection_options didn't exist yet;
 * now that it does (2025_01_12_000004), the real FK constraint goes on.
 * nullOnDelete, not cascade — deleting the winning option after a
 * decision was made should clear the pointer, not take the whole board
 * down with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('selection_boards', function (Blueprint $table) {
            $table->foreign('decided_option_id')
                ->references('id')->on('selection_options')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('selection_boards', function (Blueprint $table) {
            $table->dropForeign(['decided_option_id']);
        });
    }
};
