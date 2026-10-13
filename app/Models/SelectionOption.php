<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelectionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'selection_board_id', 'label', 'product_id', 'price', 'notes', 'file_document_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(SelectionBoard::class, 'selection_board_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'file_document_id');
    }
}
