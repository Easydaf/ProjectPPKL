<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $qcManager = User::create([
            'name' => 'QC Manager',
            'email' => 'qc@snackcheck.com',
            'password' => Hash::make('password'),
            'role' => 'qc_manager',
        ]);

        Batch::create([
            'user_id' => $qcManager->id,
            'batch_number' => 'BATCH-001',
            'status' => 'menunggu_review',
        ]);
    }
}
