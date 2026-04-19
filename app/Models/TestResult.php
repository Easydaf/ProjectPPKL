<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TestResultStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'parameter_id',
    'analyst_id',
    'result_value',
    'is_compliant',
    'deviation_note',
    'attachment_path',
    'status',
    'submitted_at',
])]
class TestResult extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'result_value' => 'decimal:4',
            'is_compliant' => 'boolean',
            'status' => TestResultStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'parameter_id');
    }

    public function analyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }
}
