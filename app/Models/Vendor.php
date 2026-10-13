<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'vendor_code', 'company_name', 'contact_person', 'phone', 'email',
        'address', 'category', 'bank_details', 'tax_info', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'bank_details' => 'array',
        ];
    }

    public function logAttributes(): array
    {
        return ['company_name', 'category', 'phone', 'email'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'vendor_products')->withPivot('vendor_price')->withTimestamps();
    }

    public function vendorQuotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function vendorInvoices(): HasMany
    {
        return $this->hasMany(VendorInvoice::class);
    }
}
