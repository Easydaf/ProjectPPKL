<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parameter_id')->constrained('test_parameters')->cascadeOnDelete();
            $table->foreignId('analyst_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('result_value', 18, 4)->nullable();
            $table->boolean('is_compliant')->default(false);
            $table->text('deviation_note')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'verified'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'parameter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
