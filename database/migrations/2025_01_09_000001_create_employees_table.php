<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // IMPORTANT — naming-collision risk, read before touching
            // this column: `employee_id` here is a plain STRING business
            // code (e.g. "EMP-000001", issued by NumberingService under
            // the `EMP` key) — it is NOT a foreign key and has nothing to
            // do with this table's own auto-increment `id` primary key.
            // Every OTHER table in this phase (attendance, leaves,
            // payrolls) also has a column literally called `employee_id`,
            // but on those tables it IS a real foreign key pointing at
            // `employees.id` (the integer PK), never at this string
            // column. ARCHITECTURE.md §3.9 names both columns
            // `employee_id` verbatim, so this dual meaning is inherited
            // from the spec, not an accident introduced here — don't
            // "fix" it by renaming one of them without re-reading this
            // comment on both sides first.
            $table->string('employee_id')->unique();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->date('joining_date');
            $table->decimal('salary', 14, 2)->default(0);
            $table->string('employment_type'); // App\Enums\EmploymentType
            $table->string('emergency_contact')->nullable();
            $table->string('status')->default('active'); // App\Enums\EmployeeStatus
            $table->softDeletes();
            $table->timestamps();

            // Default name "employees_department_id_status_index" (36
            // chars) — well under MySQL's 64-char limit, left as-is.
            $table->index(['department_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
