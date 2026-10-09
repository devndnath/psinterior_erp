<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Not written to directly — always go through
 * App\Services\NumberingService::next($key), which wraps the increment in
 * a locked transaction (spec §54: "must be safe under concurrent
 * requests... not simple max()+1 logic").
 */
class DocumentSequence extends Model
{
    protected $fillable = ['key', 'prefix', 'last_number', 'padding'];
}
