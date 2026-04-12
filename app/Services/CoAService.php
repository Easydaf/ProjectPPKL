<?php

declare(strict_types=1);

namespace App\Services;

class CoAService
{
    public function generate(int $batchId): string
    {
        return "coa/generated-batch-{$batchId}.pdf";
    }
}
