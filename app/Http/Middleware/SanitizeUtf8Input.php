<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scrubs every incoming request value to valid UTF-8 before it reaches a
 * route or a Livewire component's public properties.
 *
 * Root cause this guards against: a request field carrying an invalid
 * UTF-8 byte sequence (most often from a browser extension, clipboard
 * manager, or input-method quirk mangling pasted text — this is NOT
 * caused by typing in a non-Latin script such as Bengali, which is
 * always valid multi-byte UTF-8) gets set onto a Livewire component
 * property untouched. On the very next render, Livewire serializes the
 * component's full state back to the browser as JSON
 * (Symfony\Component\HttpFoundation\JsonResponse::setData()), and
 * json_encode() throws InvalidArgumentException("Malformed UTF-8
 * characters, possibly incorrectly encoded") the moment it hits that
 * byte sequence — a full 500 on every subsequent interaction with that
 * component, including the Description fields on Quotations, Invoices,
 * Purchase Orders, BOQs, Contracts and Change Orders, which all share
 * this same line-items pattern.
 *
 * Fixed at the edge (here) rather than per-field: re-encoding UTF-8 to
 * UTF-8 forces PHP to walk the byte stream and substitute only the
 * invalid sequences (leaving every valid character — Bengali included —
 * untouched), so this is the single point that protects every current
 * and future form without having to patch each one individually.
 *
 * Livewire's `/livewire/update` endpoint posts its payload as JSON, so
 * both the JSON input bag and the classic form/query bags are handled.
 */
class SanitizeUtf8Input
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson()) {
            $request->json()->replace($this->sanitize($request->json()->all()));
        } else {
            $request->request->replace($this->sanitize($request->request->all()));
        }

        $request->query->replace($this->sanitize($request->query->all()));

        return $next($request);
    }

    private function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $v) => $this->sanitize($v), $value);
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
