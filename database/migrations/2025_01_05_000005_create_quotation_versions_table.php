<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            // The quotation's `version` number *at the time this snapshot
            // was taken* — i.e. the version being superseded, not the new
            // one (QuotationService::reviseAfterSent).
            $table->unsignedInteger('version');
            $table->json('snapshot'); // full item list + totals + status at that point
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['quotation_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_versions');
    }
};
