<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the date windows the dashboard, CRM overview, calendar,
 * calls page and reports filter on across all projects. The existing
 * indexes all lead with project_id / lead_id / client_id, which doesn't
 * help an "everything this month" or "calls due today" query.
 */
return new class extends Migration
{
    private const INDEXES = [
        'lead_followups' => ['status', 'date'],
        'leads' => ['created_at'],
        'payments' => ['date'],
        'expenses' => ['date'],
        'site_visits' => ['visit_date'],
        'meetings' => ['date'],
        'invoices' => ['status', 'due_date'],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            Schema::table($table, fn (Blueprint $t) => $t->index($columns));
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            Schema::table($table, fn (Blueprint $t) => $t->dropIndex($columns));
        }
    }
};
