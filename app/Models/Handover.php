<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Handover extends Model
{
    use HasFactory;


    protected $fillable = ['project_id', 'handover_date', 'notes', 'client_confirmed_at'];

    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
            'client_confirmed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isConfirmed(): bool
    {
        return $this->client_confirmed_at !== null;
    }
}
