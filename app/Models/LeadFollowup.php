<?php

namespace App\Models;

use App\Enums\CallOutcome;
use App\Enums\FollowupStatus;
use App\Enums\FollowupType;
use App\Enums\LeadStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The CRM's one call / follow-up log. A row belongs to a lead, a client,
 * or both — a converted lead's history carries its client_id too — so the
 * lead page, the client page, the Calls page, the CRM overview and the
 * calendar all read and write the same rows. A pending row is a
 * scheduled call due on `date`; a done or missed one is history.
 */
class LeadFollowup extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'lead_id', 'client_id', 'date', 'time', 'type', 'notes',
        'next_followup_date', 'assigned_to', 'status', 'outcome',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'next_followup_date' => 'date',
            'type' => FollowupType::class,
            'status' => FollowupStatus::class,
            'outcome' => CallOutcome::class,
        ];
    }

    public function logAttributes(): array
    {
        return ['lead_id', 'client_id', 'date', 'type', 'status', 'outcome', 'next_followup_date'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOverdue(): bool
    {
        return $this->status === FollowupStatus::Pending && $this->date->isPast();
    }

    /** The call types — what the Calls page and call counts are about. */
    public const CALL_TYPES = [FollowupType::Call, FollowupType::WhatsApp];

    public function scopeCalls(Builder $query): Builder
    {
        return $query->whereIn('type', array_map(fn (FollowupType $t) => $t->value, self::CALL_TYPES));
    }

    /**
     * Pending calls still worth making: any client's, or an open lead's
     * (a lost or won-but-unconverted lead's leftover calls drop out).
     */
    public function scopeToMake(Builder $query): Builder
    {
        $open = array_map(fn (LeadStatus $s) => $s->value, array_filter(LeadStatus::cases(), fn (LeadStatus $s) => $s->isOpen()));

        return $query->where('status', FollowupStatus::Pending->value)
            ->where(fn ($q) => $q->whereNotNull('client_id')
                ->orWhereHas('lead', fn ($l) => $l->whereIn('status', $open)));
    }

    /** Only the rows this user may see: lead calls need view_leads, client calls view_clients. */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $leads = (bool) $user?->can('view_leads');
        $clients = (bool) $user?->can('view_clients');

        return match (true) {
            $leads && $clients => $query,
            $leads => $query->whereNotNull('lead_id'),
            $clients => $query->whereNotNull('client_id'),
            default => $query->whereRaw('1 = 0'),
        };
    }

    // The person on the other end: the client once there is one, else the lead.

    public function contactKind(): string
    {
        return $this->client_id ? 'Client' : 'Lead';
    }

    public function contactName(): ?string
    {
        return $this->client_id ? $this->client?->name : $this->lead?->name;
    }

    public function contactNo(): ?string
    {
        return $this->client_id ? $this->client?->client_no : $this->lead?->lead_no;
    }

    public function contactPhone(): ?string
    {
        return ($this->client_id ? $this->client?->phone : null) ?: $this->lead?->phone;
    }

    public function contactUrl(): ?string
    {
        return match (true) {
            (bool) $this->client_id => route('clients.show', $this->client_id),
            (bool) $this->lead_id => route('leads.show', $this->lead_id),
            default => null,
        };
    }

    /** The relations contact*() read — eager-load these on lists. */
    public const CONTACT_RELATIONS = ['lead:id,lead_no,name,phone,status', 'client:id,client_no,name,phone'];

    public function isCall(): bool
    {
        return in_array($this->type, self::CALL_TYPES, true);
    }

    public static function telUrl(?string $phone): ?string
    {
        $digits = preg_replace('/[^\d+]/', '', (string) $phone);

        return $digits !== '' ? 'tel:'.$digits : null;
    }

    /** wa.me link; local Bangladeshi numbers (01XXXXXXXXX) get the 880 country code. */
    public static function whatsappUrl(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            $digits = '88'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }
}
