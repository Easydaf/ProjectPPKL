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
        Schema::create('batches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number')->unique();
            $table->date('production_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->integer('sample_quantity')->nullable();
            $table->enum('status', [
                'menunggu_penerimaan',
                'sedang_diuji',
                'menunggu_review',
                'lulus',
                'tidak_lulus',
                'ditahan',
                'uji_ulang',
            ])->default('menunggu_review');
            $table->timestamps();

            $table->index('product_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
