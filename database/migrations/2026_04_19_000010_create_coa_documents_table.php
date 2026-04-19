<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('decision_id')->nullable()->constrained('test_decisions')->nullOnDelete();
            $table->string('coa_number')->unique();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_documents');
    }
};
