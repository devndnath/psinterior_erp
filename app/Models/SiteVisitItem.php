<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisitItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_visit_id', 'area_name', 'length', 'width', 'height', 'area', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'area' => 'decimal:2',
        ];
    }

    /**
     * Area is auto-calculated from length × width whenever either changes
     * (spec §7: "Automatically calculate area where applicable"). This is
     * a model-level safety net — SiteVisitService also sets it explicitly
     * when building the record from a form — so area can never silently
     * drift out of sync with length/width, however the row is saved.
     */
    protected static function booted(): void
    {
        static::saving(function (self $item) {
            if ($item->length !== null && $item->width !== null) {
                $item->area = round((float) $item->length * (float) $item->width, 2);
            }
        });
    }

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class);
    }
}
