<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();

            // This `employee_id` IS a real foreign key, pointing at
            // `employees.id` (the auto-increment PK) — the standard
            // Laravel convention of naming a FK column after the
            // relation. Do not confuse this with `employees.employee_id`
            // itself, which is a plain string business code and NOT a
            // foreign key at all (see the detailed comment on that
            // column in the `employees` migration). The same column
            // name means two different things on the two tables.
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            // Full check-in/check-out moment, not just a time-of-day, so
            // a biometric API (ARCHITECTURE.md §3.9) can record the
            // actual punch timestamp without losing the date boundary.
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->string('status'); // App\Enums\AttendanceStatus
            $table->timestamps();

            // Default name "attendance_employee_id_date_unique" (34
            // chars) — under the limit, left as-is.
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
