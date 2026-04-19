<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'variant',
    'risk_level',
])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'variant' => 'string',
            'risk_level' => 'string',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function testParameters(): HasMany
    {
        return $this->hasMany(TestParameter::class);
    }
}
