<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type'); // App\Enums\LeaveType
            $table->date('from_date');
            $table->date('to_date');
            $table->text('reason')->nullable();
            // Two-stage approval per ARCHITECTURE.md §3.9: both columns
            // use App\Enums\LeaveApprovalStatus independently. No
            // ordering between the two is enforced at the schema level —
            // see LeaveService's docblock for the deliberate decision not
            // to enforce it in the service layer either.
            $table->string('manager_status')->default('pending'); // App\Enums\LeaveApprovalStatus
            $table->string('hr_status')->default('pending'); // App\Enums\LeaveApprovalStatus
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
