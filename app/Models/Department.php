<?php

namespace App\Models;

use App\Models\Concerns\HasCompanyScope;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasCompanyScope, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = ['company_id', 'name', 'code'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }
}
