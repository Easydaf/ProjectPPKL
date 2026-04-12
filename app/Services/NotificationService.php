<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendToAnalis(int $batchId): void
    {
        Log::info('Notifikasi telah dikirim ke analis.', [
            'batch_id' => $batchId,
        ]);
    }
}
