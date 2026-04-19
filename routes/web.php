<?php

use App\Enums\DecisionStatus;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SubmitTestController;
use App\Models\Batch;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $batch = null;

    try {
        $batch = Batch::query()
            ->with(['testDecision', 'auditTrails.user'])
            ->latest('id')
            ->first();
    } catch (Throwable) {
        $batch = null;
    }

    return view('reviews.show', [
        'batch' => $batch,
        'decisionOptions' => DecisionStatus::cases(),
    ]);
});

Route::get('/batches/{batch_id}/review', [ReviewController::class, 'show'])
    ->whereNumber('batch_id')
    ->name('batches.review.show');

Route::post('/batches/{batch_id}/submit-test', [SubmitTestController::class, 'submit'])
    ->whereNumber('batch_id')
    ->name('batches.submit-test');

Route::put('/batches/{batch_id}/review', [ReviewController::class, 'update'])
    ->whereNumber('batch_id')
    ->name('batches.review.update');

Route::patch('/batches/{batch_id}/review', [ReviewController::class, 'update'])
    ->whereNumber('batch_id')
    ->name('batches.review.patch');
