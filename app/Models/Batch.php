<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'product_id',
    'user_id',
    'batch_number',
    'production_date',
    'expiration_date',
    'sample_quantity',
    'status',
])]
class Batch extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'expiration_date' => 'date',
            'sample_quantity' => 'integer',
            'status' => BatchStatus::class,
        ];
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(AuditTrail::class, 'record_id')->where('table_name', 'batches');
    }

    public function testDecision(): HasOne
    {
        return $this->hasOne(TestDecision::class);
    }
}
