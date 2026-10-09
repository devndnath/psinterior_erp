<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backs App\Services\NumberingService::next(). One row per numbering
     * key (LEAD, CLIENT, PRJ, SV, QUO, CON, INV, PAY, PO, GRN, EXP, MR,
     * SNAG, ...). `last_number` is only ever incremented inside a
     * `lockForUpdate()` transaction — never via max()+1 (spec §54).
     */
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('prefix');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->unsignedTinyInteger('padding')->default(6);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
