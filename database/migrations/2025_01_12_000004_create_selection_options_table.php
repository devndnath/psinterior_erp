<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('selection_board_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            // Optional link to a real catalog item — most finish/tile
            // options (vendor samples, one-off picks) won't have a SKU,
            // so this stays nullable rather than forcing every option
            // through the Product catalog.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('price', 14, 2)->nullable();
            $table->text('notes')->nullable();
            // One reference image per option (1:1), same pattern as
            // DesignRevision::file_document_id — not morphMany, since an
            // option owns exactly one image, never several.
            $table->foreignId('file_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();

            $table->index('selection_board_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_options');
    }
};
