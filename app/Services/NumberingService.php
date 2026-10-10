<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

/**
 * Generates document numbers (LEAD-000001, PRJ-000001, INV-000001, ...)
 * safely under concurrent requests (spec §54). The increment happens
 * inside a transaction with `lockForUpdate()`, so two requests racing for
 * the same key can never receive the same number — unlike a naive
 * `max(id)+1` read.
 *
 * Usage: app(NumberingService::class)->next('LEAD') -> "LEAD-000001"
 */
class NumberingService
{
    /**
     * @param  string  $key  e.g. LEAD, CLIENT, PRJ, SV, QUO, CON, INV, PAY, PO, GRN, EXP, MR, SNAG, VEN, PR
     */
    public function next(string $key): string
    {
        return DB::transaction(function () use ($key) {
            $sequence = DocumentSequence::query()
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                // Self-registers on first use so new modules never need a
                // manual seeder entry before they can issue numbers.
                $sequence = DocumentSequence::create([
                    'key' => $key,
                    'prefix' => $key,
                    'last_number' => 0,
                    'padding' => 6,
                ]);

                $sequence = DocumentSequence::query()
                    ->where('id', $sequence->id)
                    ->lockForUpdate()
                    ->first();
            }

            $next = $sequence->last_number + 1;

            $sequence->update(['last_number' => $next]);

            return sprintf(
                '%s-%s',
                $sequence->prefix,
                str_pad((string) $next, $sequence->padding, '0', STR_PAD_LEFT)
            );
        });
    }

    public function register(string $key, string $prefix, int $padding = 6): DocumentSequence
    {
        return DocumentSequence::firstOrCreate(
            ['key' => $key],
            ['prefix' => $prefix, 'last_number' => 0, 'padding' => $padding]
        );
    }

    /**
     * Changes the prefix a key issues going forward (e.g. the Settings ->
     * Numbering Prefixes tab). Already-issued numbers are untouched —
     * only `prefix` changes, never `last_number`.
     */
    public function updatePrefix(string $key, string $prefix): DocumentSequence
    {
        $sequence = $this->register($key, $prefix);
        $sequence->update(['prefix' => $prefix]);

        return $sequence;
    }
}
