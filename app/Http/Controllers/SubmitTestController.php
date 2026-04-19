<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BatchStatus;
use App\Models\AuditTrail;
use App\Models\Batch;
use App\Services\NotificationService;
use BackedEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class SubmitTestController extends Controller
{
    public function submit(int $batch_id, NotificationService $notificationService): JsonResponse
    {
        $batch = Batch::query()->find($batch_id);

        if ($batch === null) {
            return response()->json([
                'message' => 'Batch tidak ditemukan.',
            ], 404);
        }

        try {
            DB::transaction(function () use ($batch): void {
                $oldStatus = $batch->status instanceof BackedEnum
                    ? $batch->status->value
                    : (string) $batch->status;

                $batch->update([
                    'status' => BatchStatus::MenungguReview->value,
                ]);

                AuditTrail::query()->create([
                    'user_id' => $batch->user_id,
                    'table_name' => 'batches',
                    'record_id' => $batch->id,
                    'action' => 'submitted_for_review',
                    'old_values' => [
                        'status' => $oldStatus,
                    ],
                    'new_values' => [
                        'status' => BatchStatus::MenungguReview->value,
                    ],
                ]);
            });

            $freshBatch = $batch->fresh();

            if ($freshBatch !== null) {
                $notificationService->notifyQcManagerReviewReady($freshBatch);
            }
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Terjadi kesalahan saat mengirim hasil tes.',
            ], 500);
        }

        return response()->json([
            'message' => 'Hasil tes berhasil dikirim untuk direview.',
            'data' => [
                'batch_id' => $batch->id,
                'status' => BatchStatus::MenungguReview->value,
            ],
        ]);
    }
}
