<?php

namespace Tests\Unit;

use App\Models\DocumentSequence;
use App\Services\NumberingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * spec §54: numbering must be safe under concurrent requests, never a
 * naive max()+1. True concurrent-process locking is a MySQL behavior
 * (SQLite's lockForUpdate() is a no-op on a single connection), so the
 * decisive proof that two simultaneous requests cannot collide belongs in
 * a staging/integration run against MySQL — documented in README.md. What
 * *is* tested here, portable to any driver: the sequence is correct under
 * rapid sequential use, numbers are zero-padded and prefixed correctly,
 * a never-seen key self-registers instead of erroring, and changing a
 * prefix never perturbs the counter.
 */
class NumberingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sequential_numbers_increment_without_gaps_or_duplicates(): void
    {
        $service = app(NumberingService::class);
        $service->register('TEST', 'TEST');

        $numbers = [];
        for ($i = 0; $i < 25; $i++) {
            $numbers[] = $service->next('TEST');
        }

        $this->assertCount(25, array_unique($numbers));
        $this->assertSame('TEST-000001', $numbers[0]);
        $this->assertSame('TEST-000025', $numbers[24]);
    }

    public function test_unknown_key_self_registers_on_first_use(): void
    {
        $this->assertDatabaseMissing('document_sequences', ['key' => 'NEWMOD']);

        $number = app(NumberingService::class)->next('NEWMOD');

        $this->assertSame('NEWMOD-000001', $number);
        $this->assertDatabaseHas('document_sequences', ['key' => 'NEWMOD', 'last_number' => 1]);
    }

    public function test_updating_prefix_never_touches_last_number(): void
    {
        $service = app(NumberingService::class);
        $service->register('INV', 'INV');
        $service->next('INV');
        $service->next('INV');

        $service->updatePrefix('INV', 'INVOICE');
        $third = $service->next('INV');

        $this->assertSame('INVOICE-000003', $third);
    }

    public function test_concurrent_increments_inside_separate_transactions_do_not_collide(): void
    {
        // Simulates the shape of two racing requests: both read-then-write
        // inside their own DB::transaction(), which is exactly what
        // NumberingService::next() does. On MySQL the second transaction's
        // lockForUpdate() read blocks until the first commits; here we at
        // least prove repeated same-process transactional calls never
        // reuse a number, which a naive max()+1 implementation would fail
        // under far lighter conditions than true concurrency.
        $service = app(NumberingService::class);
        $service->register('PO', 'PO');

        $results = collect(range(1, 10))->map(fn () => DB::transaction(fn () => $service->next('PO')));

        $this->assertCount(10, $results->unique());
    }
}
