<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DecisionStatus;
use App\Models\Batch;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendToAnalis(int $batchId): void
    {
        Log::info('Notifikasi telah dikirim ke analis.', [
            'batch_id' => $batchId,
        ]);
    }

    public function notifyQcManagerReviewReady(Batch $batch): void
    {
        Log::info('Batch baru siap direview oleh QC Manager.', [
            'batch_id' => $batch->id,
            'batch_number' => $batch->batch_number,
        ]);
    }

    public function broadcastDecision(Batch $batch): void
    {
        $batch->loadMissing(['testDecision', 'coaDocument']);

        $decision = $batch->testDecision;

        if ($decision === null) {
            return;
        }

        if ($decision->decision_status === DecisionStatus::Lulus) {
            Log::info('Notifikasi keputusan lulus telah dikirim ke Manajer Produksi dan Staff Gudang.', [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'coa_path' => $batch->coaDocument?->file_path ?? $decision->coa_path,
            ]);

            return;
        }

        if ($decision->decision_status === DecisionStatus::TidakLulus) {
            Log::info('Notifikasi keputusan tidak lulus telah dikirim ke Manajer Produksi.', [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'reason' => $decision->notes,
                'recommendation' => $decision->action_recommendation,
            ]);

            return;
        }

        if ($decision->decision_status === DecisionStatus::UjiUlang) {
            $this->sendToAnalis($batch->id);

            Log::info('Notifikasi keputusan uji ulang telah disiapkan untuk pihak terkait.', [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
            ]);

            return;
        }

        Log::info('Notifikasi keputusan batch telah diproses.', [
            'batch_id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'decision' => $decision->decision_status->value,
        ]);
    }
}
