<?php

namespace App\Models;

use App\Enums\UserType;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasDocuments, HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    /**
     * Spatie roles/permissions are only meaningful on the `web` (staff)
     * guard in Phase 1 — client/vendor portal users are never assigned
     * roles, their access is just "is this their own record" (spec §78
     * rules 10-11), enforced in Policies, not via permissions.
     */
    protected string $guard_name = 'web';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar_path',
        'user_type',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'user_type' => UserType::class,
        ];
    }

    public function logAttributes(): array
    {
        // Never diff the password hash into the audit log.
        return ['name', 'email', 'phone', 'user_type', 'is_active', 'company_id'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Inverse of `clients.user_id` (nullable FK back to this table, built
     * in Phase 2/8) — resolves "which Client am I" for a logged-in
     * `client`-guard user. Every Client Portal component starts with
     * `auth()->user()->client` and `abort_unless($client, 403, ...)`
     * (Phase 11 — see ClientPortal in PHASE11_SUMMARY.md).
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    /**
     * Inverse of `vendors.user_id`, same reasoning as client() above,
     * for a logged-in `vendor`-guard user.
     */
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'user_id');
    }

    public function isStaff(): bool
    {
        return $this->user_type === UserType::Staff;
    }

    public function isClient(): bool
    {
        return $this->user_type === UserType::Client;
    }

    public function isVendor(): bool
    {
        return $this->user_type === UserType::Vendor;
    }

    public function scopeStaff($query)
    {
        return $query->where('user_type', UserType::Staff);
    }

    public function scopeClients($query)
    {
        return $query->where('user_type', UserType::Client);
    }

    public function scopeVendors($query)
    {
        return $query->where('user_type', UserType::Vendor);
    }
}
