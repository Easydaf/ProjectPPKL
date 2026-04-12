<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DecisionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'user_id',
    'decision_status',
    'action_recommendation',
    'notes',
    'coa_path',
])]
class TestDecision extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'decision_status' => DecisionStatus::class,
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
