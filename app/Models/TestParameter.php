<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ParameterCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'category',
    'parameter_name',
    'unit',
    'min_value',
    'max_value',
    'is_active',
])]
class TestParameter extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'category' => ParameterCategory::class,
            'min_value' => 'decimal:4',
            'max_value' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class, 'parameter_id');
    }
}
