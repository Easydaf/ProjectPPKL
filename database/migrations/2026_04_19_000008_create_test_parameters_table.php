<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_parameters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['mikrobiologi', 'kimia', 'fisik', 'organoleptik']);
            $table->string('parameter_name');
            $table->string('unit')->nullable();
            $table->decimal('min_value', 10, 4)->nullable();
            $table->decimal('max_value', 10, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_parameters');
    }
};
