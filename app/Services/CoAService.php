<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DecisionStatus;
use App\Models\Batch;
use App\Models\CoaDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CoAService
{
    public function generate(int $batchId): bool
    {
        $batch = Batch::query()
            ->with([
                'product',
                'submitter',
                'testResults.parameter',
                'testDecision',
            ])
            ->find($batchId);

        if ($batch === null || $batch->testDecision === null) {
            return false;
        }

        if ($batch->testDecision->decision_status !== DecisionStatus::Lulus) {
            return false;
        }

        $coaNumber = sprintf('COA-%s-%d', now()->format('Ymd'), $batch->id);
        $fileName = $coaNumber . '.pdf';
        $filePath = 'coa/' . $fileName;

        $pdf = Pdf::loadView('coa.document', [
            'batch' => $batch,
            'coaNumber' => $coaNumber,
            'generatedAt' => now(),
        ])->setPaper('a4');

        Storage::disk('public')->put($filePath, $pdf->output());

        CoaDocument::query()->updateOrCreate(
            ['batch_id' => $batch->id],
            [
                'decision_id' => $batch->testDecision->id,
                'coa_number' => $coaNumber,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'issued_by' => $batch->testDecision->user_id,
                'issued_at' => now(),
            ]
        );

        $batch->testDecision->forceFill([
            'coa_path' => $filePath,
        ])->save();

        return true;
    }
}
