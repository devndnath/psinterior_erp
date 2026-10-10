<?php

namespace App\Models;

use App\Enums\InspectionResult;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id', 'area', 'inspection_date', 'inspector_id', 'result', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'result' => InspectionResult::class,
            'inspection_date' => 'date',
        ];
    }

    public function logAttributes(): array
    {
        return ['result', 'area'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionItem::class);
    }

    /** An inspection can have several snags raised against it. */
    public function snags(): HasMany
    {
        return $this->hasMany(Snag::class);
    }
}
