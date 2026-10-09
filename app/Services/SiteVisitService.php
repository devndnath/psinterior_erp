<?php

namespace App\Services;

use App\Models\SiteVisit;
use Illuminate\Support\Facades\DB;

class SiteVisitService
{
    public function __construct(protected NumberingService $numbering) {}

    /**
     * @param  array  $data  site visit fields
     * @param  array<int, array{area_name:string,length:?float,width:?float,height:?float,notes:?string}>  $rooms
     */
    public function create(array $data, array $rooms = []): SiteVisit
    {
        return DB::transaction(function () use ($data, $rooms) {
            $data['sv_no'] = $this->numbering->next('SV');
            $visit = SiteVisit::create($data);

            foreach ($rooms as $room) {
                $visit->items()->create($room);
            }

            return $visit->fresh('items');
        });
    }

    public function update(SiteVisit $visit, array $data, ?array $rooms = null): SiteVisit
    {
        return DB::transaction(function () use ($visit, $data, $rooms) {
            $visit->update($data);

            if ($rooms !== null) {
                // Replace wholesale rather than diffing — a site visit's
                // room list is re-entered as a set each edit, not patched
                // field by field, so this avoids leaving orphaned rows
                // from a room the user removed in the form.
                $visit->items()->delete();

                foreach ($rooms as $room) {
                    $visit->items()->create($room);
                }
            }

            return $visit->fresh('items');
        });
    }
}
