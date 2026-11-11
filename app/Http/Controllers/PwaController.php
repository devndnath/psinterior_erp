<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Web app manifest — what lets the ERP be installed to a phone or tablet
 * home screen and open full-screen like an app (and what an app wrapper
 * reads for the name, icon and colours). Served by a route rather than a
 * static file so it carries the company name from Settings.
 */
class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $name = Setting::get('company.name', config('app.name'));

        return response()->json([
            'name' => $name,
            'short_name' => Str::limit($name, 12, ''),
            'description' => 'Interior & Architecture ERP',
            'id' => '/',
            'start_url' => '/dashboard',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#EEEDE8',
            'theme_color' => '#EEEDE8',
            'icons' => [
                ['src' => asset('icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('icons/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('icons/icon-maskable-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            // Long-press shortcuts on the home-screen icon.
            'shortcuts' => [
                ['name' => 'Calls', 'url' => '/calls', 'icons' => [['src' => asset('icons/icon-192.png'), 'sizes' => '192x192']]],
                ['name' => 'New lead', 'url' => '/leads/create', 'icons' => [['src' => asset('icons/icon-192.png'), 'sizes' => '192x192']]],
                ['name' => 'Projects', 'url' => '/projects', 'icons' => [['src' => asset('icons/icon-192.png'), 'sizes' => '192x192']]],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
