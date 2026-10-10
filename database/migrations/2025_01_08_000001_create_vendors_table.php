<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code')->unique();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            // Free-text trade/category label (e.g. "Hardware", "Furniture")
            // — not an FK to product_categories, a vendor can span several.
            $table->string('category')->nullable();
            $table->json('bank_details')->nullable();
            $table->string('tax_info')->nullable();
            // Future vendor-portal login on the `vendor` guard
            // (config/auth.php already reserves it) — nullable, mirrors
            // how clients.user_id is nullable until a client is invited.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
