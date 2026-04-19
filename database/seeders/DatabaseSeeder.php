<?php

namespace Database\Seeders;

use App\Enums\ParameterCategory;
use App\Enums\TestResultStatus;
use App\Models\Batch;
use App\Models\Product;
use App\Models\TestParameter;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::create([
            'name' => 'Keripik Kentang',
            'variant' => 'Original',
            'risk_level' => 'medium',
        ]);

        $qcManager = User::create([
            'name' => 'QC Manager',
            'email' => 'qc@snackcheck.com',
            'password' => Hash::make('password'),
            'role' => 'qc_manager',
        ]);

        $analyst = User::create([
            'name' => 'Analis Lab',
            'email' => 'analis@snackcheck.com',
            'password' => Hash::make('password'),
            'role' => 'analis_lab',
        ]);

        $batch = Batch::create([
            'product_id' => $product->id,
            'user_id' => $qcManager->id,
            'batch_number' => 'BATCH-001',
            'production_date' => now()->toDateString(),
            'expiration_date' => now()->addMonths(6)->toDateString(),
            'sample_quantity' => 3,
            'status' => 'menunggu_review',
        ]);

        $parameter = TestParameter::create([
            'product_id' => $product->id,
            'category' => ParameterCategory::Kimia->value,
            'parameter_name' => 'Kadar Air',
            'unit' => '%',
            'min_value' => 0.0000,
            'max_value' => 5.0000,
            'is_active' => true,
        ]);

        TestResult::create([
            'batch_id' => $batch->id,
            'parameter_id' => $parameter->id,
            'analyst_id' => $analyst->id,
            'result_value' => 3.2500,
            'is_compliant' => true,
            'deviation_note' => null,
            'attachment_path' => 'attachments/batch-001/kadar-air.pdf',
            'status' => TestResultStatus::Submitted->value,
            'submitted_at' => now(),
        ]);
    }
}
