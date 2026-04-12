<?php

use App\Enums\DecisionStatus;
use App\Http\Controllers\ReviewController;
use App\Models\Batch;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $batch = Batch::query()
        ->with(['testDecision', 'auditTrails.user'])
        ->latest('id')
        ->first();

    return view('reviews.show', [
        'batch' => $batch,
        'decisionOptions' => DecisionStatus::cases(),
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/batches/{batch_id}/review', [ReviewController::class, 'show'])
        ->whereNumber('batch_id')
        ->name('batches.review.show');

    Route::patch('/batches/{batch_id}/review', [ReviewController::class, 'update'])
        ->whereNumber('batch_id')
        ->name('batches.review.update');
});
