<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'decision_id',
    'coa_number',
    'file_name',
    'file_path',
    'issued_by',
    'issued_at',
])]
class CoaDocument extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(TestDecision::class, 'decision_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
