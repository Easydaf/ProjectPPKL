<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('decision_status', [
                'lulus',
                'tidak_lulus',
                'ditahan',
                'uji_ulang',
            ]);
            $table->string('action_recommendation')->nullable();
            $table->text('notes')->nullable();
            $table->string('coa_path')->nullable();
            $table->timestamps();

            $table->unique('batch_id');
            $table->index('decision_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_decisions');
    }
};
