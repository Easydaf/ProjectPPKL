<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DecisionStatus;
use App\Http\Requests\ReviewBatchRequest;
use App\Models\AuditTrail;
use App\Models\Batch;
use App\Models\TestDecision;
use App\Services\CoAService;
use App\Services\NotificationService;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReviewController extends Controller
{
    public function show(int $batch_id): View
    {
        $batch = Batch::query()
            ->with(['testDecision', 'auditTrails.user'])
            ->findOrFail($batch_id);

        return view('reviews.show', [
            'batch' => $batch,
            'decisionOptions' => DecisionStatus::cases(),
        ]);
    }

    public function update(
        ReviewBatchRequest $request,
        int $batch_id,
        CoAService $coAService,
        NotificationService $notificationService,
    ): JsonResponse {
        $batch = Batch::query()->find($batch_id);

        if ($batch === null) {
            return response()->json([
                'message' => 'Batch tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validated();
        $updatedStatus = null;

        try {
            DB::transaction(function () use ($batch, $validated, $request, &$updatedStatus): void {
                $oldStatus = $batch->status instanceof BackedEnum
                    ? $batch->status->value
                    : (string) $batch->status;

                $updatedStatus = $validated['keputusan_akhir'];

                $batch->update([
                    'status' => $updatedStatus,
                ]);

                TestDecision::query()->updateOrCreate(
                    ['batch_id' => $batch->id],
                    [
                        'user_id' => $request->user()?->id,
                        'decision_status' => $updatedStatus,
                        'action_recommendation' => $validated['tindakan_rekomendasi'] ?? null,
                        'notes' => $validated['catatan'] ?? null,
                    ]
                );

                AuditTrail::query()->create([
                    'user_id' => $request->user()?->id,
                    'table_name' => 'batches',
                    'record_id' => $batch->id,
                    'action' => 'final_review_updated',
                    'old_values' => [
                        'status' => $oldStatus,
                    ],
                    'new_values' => [
                        'status' => $updatedStatus,
                        'tindakan_rekomendasi' => $validated['tindakan_rekomendasi'] ?? null,
                        'catatan' => $validated['catatan'] ?? null,
                    ],
                ]);
            });

            if ($updatedStatus === DecisionStatus::Lulus->value) {
                $coAPath = $coAService->generate($batch->id);

                TestDecision::query()
                    ->where('batch_id', $batch->id)
                    ->update(['coa_path' => $coAPath]);
            }

            if ($updatedStatus === DecisionStatus::UjiUlang->value) {
                $notificationService->sendToAnalis($batch->id);
            }
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui keputusan akhir batch.',
            ], 500);
        }

        return response()->json([
            'message' => 'Keputusan akhir batch berhasil diperbarui.',
            'data' => [
                'batch_id' => $batch->id,
                'status' => $this->normalizeStatusValue($batch->fresh()?->status),
            ],
        ]);
    }

    private function normalizeStatusValue(mixed $status): ?string
    {
        if ($status instanceof BackedEnum) {
            return (string) $status->value;
        }

        if (is_string($status)) {
            return $status;
        }

        return null;
    }
}
