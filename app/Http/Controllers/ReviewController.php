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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReviewController extends Controller
{
    public function show(int $batch_id): JsonResponse
    {
        $batch = Batch::query()
            ->with([
                'product',
                'submitter',
                'testResults.parameter',
                'testDecision.coaDocument',
                'coaDocument',
                'auditTrails.user',
            ])
            ->find($batch_id);

        if ($batch === null) {
            return response()->json([
                'message' => 'Batch tidak ditemukan.',
            ], 404);
        }

        $testResults = $batch->testResults->map(function ($testResult): array {
            $parameter = $testResult->parameter;
            $isCompliant = $this->isResultCompliant(
                $testResult->result_value,
                $parameter?->min_value,
                $parameter?->max_value,
                (bool) $testResult->is_compliant,
            );

            return [
                'id' => $testResult->id,
                'parameter' => $parameter?->parameter_name,
                'category' => $parameter?->category instanceof BackedEnum ? $parameter->category->value : $parameter?->category,
                'result_value' => $testResult->result_value,
                'standard_min' => $parameter?->min_value,
                'standard_max' => $parameter?->max_value,
                'indicator' => $isCompliant ? 'Memenuhi Syarat' : 'Tidak Memenuhi Syarat',
                'attachment_path' => $testResult->attachment_path,
                'submitted_at' => $testResult->submitted_at?->toDateTimeString(),
            ];
        })->values();

        return response()->json([
            'message' => 'Detail batch berhasil diambil.',
            'data' => [
                'batch' => [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'status' => $this->normalizeStatusValue($batch->status),
                    'product' => $batch->product?->name,
                    'variant' => $batch->product?->variant,
                    'sample_quantity' => $batch->sample_quantity,
                    'production_date' => $this->formatDateValue($batch->getRawOriginal('production_date')),
                    'expiration_date' => $this->formatDateValue($batch->getRawOriginal('expiration_date')),
                ],
                'test_results' => $testResults,
                'documents' => [
                    'coa_document' => $batch->coaDocument?->file_path,
                    'coa_number' => $batch->coaDocument?->coa_number,
                ],
                'test_decision' => $batch->testDecision ? [
                    'decision_status' => $this->normalizeStatusValue($batch->testDecision->decision_status),
                    'action_recommendation' => $batch->testDecision->action_recommendation,
                    'notes' => $batch->testDecision->notes,
                    'coa_path' => $batch->testDecision->coa_path,
                ] : null,
                'audit_trails' => $batch->auditTrails->map(fn(AuditTrail $auditTrail): array => [
                    'id' => $auditTrail->id,
                    'action' => $auditTrail->action,
                    'old_values' => $auditTrail->old_values,
                    'new_values' => $auditTrail->new_values,
                    'created_at' => optional($auditTrail->created_at)?->toDateTimeString(),
                ])->values(),
            ],
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
                $coAService->generate($batch->id);
            }

            $freshBatch = $batch->fresh(['testDecision', 'coaDocument']);

            if ($freshBatch !== null) {
                $notificationService->broadcastDecision($freshBatch);
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

    private function isResultCompliant(mixed $resultValue, mixed $minValue, mixed $maxValue, bool $storedCompliance): bool
    {
        if ($resultValue === null || ($minValue === null && $maxValue === null)) {
            return $storedCompliance;
        }

        $numericValue = (float) $resultValue;
        $minimum = $minValue !== null ? (float) $minValue : null;
        $maximum = $maxValue !== null ? (float) $maxValue : null;

        if ($minimum !== null && $numericValue < $minimum) {
            return false;
        }

        if ($maximum !== null && $numericValue > $maximum) {
            return false;
        }

        return true;
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

    private function formatDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return date('Y-m-d', strtotime((string) $value));
    }
    public function requestRetest(\Illuminate\Http\Request $request, int $batch_id): JsonResponse
    {
        $batch = \App\Models\Batch::find($batch_id);

        if ($batch === null) {
            return response()->json(['message' => 'Batch tidak ditemukan.'], 404);
        }

        // PERBAIKAN: Menerjemahkan tipe data Enum Laravel menjadi teks string biasa
        $currentStatus = $batch->status instanceof \BackedEnum 
            ? $batch->status->value 
            : (string) $batch->status;

        // Validasi: hanya batch 'tidak_lulus' yang boleh di-retest
        if ($currentStatus !== 'tidak_lulus') {
            return response()->json([
                'message' => 'Error 422: Request ditolak. Retest hanya berlaku untuk batch yang tidak_lulus. Status saat ini: ' . $currentStatus,
            ], 422);
        }

        $batch->update(['status' => 'menunggu_retest']);

        \App\Models\AuditTrail::create([
            'user_id' => $request->user()?->id,
            'table_name' => 'batches',
            'record_id' => $batch->id,
            'action' => 'request_retest',
            'old_values' => ['status' => $currentStatus],
            'new_values' => ['status' => 'menunggu_retest'],
        ]);

        return response()->json([
            'message' => 'Request Re-test berhasil diajukan.',
            'data' => ['status' => 'menunggu_retest']
        ]);
    }
}
