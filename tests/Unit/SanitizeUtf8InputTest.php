<?php

namespace Tests\Unit;

use App\Http\Middleware\SanitizeUtf8Input;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Regression test for the "Malformed UTF-8 characters, possibly
 * incorrectly encoded" 500 that json_encode() throws when a Livewire
 * component tries to serialize a property containing an invalid UTF-8
 * byte sequence back into its update response.
 */
class SanitizeUtf8InputTest extends TestCase
{
    public function test_it_strips_invalid_utf8_bytes_from_a_json_request_body(): void
    {
        // "Sofa " followed by a lone 0x80 continuation byte with no lead
        // byte — not valid UTF-8 on its own, the same shape of corruption
        // a mangled paste can introduce into a Description field.
        $invalid = "Sofa \x80 set";

        $request = Request::create(
            '/livewire/update',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['items' => [['description' => null]]]),
        );
        // Build the JSON input bag by hand so we control the raw bytes —
        // json_encode() itself would refuse to encode the invalid string.
        $request->headers->set('Content-Type', 'application/json');
        $jsonBag = $request->json();
        $jsonBag->set('items.0.description', $invalid);

        $this->assertFalse(mb_check_encoding($invalid, 'UTF-8'));

        $middleware = new SanitizeUtf8Input;
        $middleware->handle($request, function (Request $req) {
            $sanitized = $req->json('items.0.description');
            $this->assertTrue(mb_check_encoding($sanitized, 'UTF-8'));
            // json_encode must now succeed where it would previously throw.
            $this->assertNotFalse(json_encode(['description' => $sanitized]));

            return response()->json(['ok' => true]);
        });
    }

    public function test_it_leaves_valid_multibyte_text_including_bengali_untouched(): void
    {
        $bengali = 'লিভিং রুম ফার্নিচার';

        $request = Request::create('/livewire/update', 'POST');
        $request->headers->set('Content-Type', 'application/json');
        $request->json()->set('items.0.description', $bengali);

        $middleware = new SanitizeUtf8Input;
        $middleware->handle($request, function (Request $req) use ($bengali) {
            $this->assertSame($bengali, $req->json('items.0.description'));

            return response()->json(['ok' => true]);
        });
    }
}
