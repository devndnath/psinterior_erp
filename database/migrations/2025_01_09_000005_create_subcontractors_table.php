<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcontractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trade'); // App\Enums\SubcontractorTrade
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            // Free text ("per sqft" / "per day" / "lump sum", ...) — not
            // an enum, since ARCHITECTURE.md doesn't enumerate a fixed
            // list for how a subcontractor is rated, unlike every other
            // status-shaped column in this phase.
            $table->string('rate_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractors');
    }
};
