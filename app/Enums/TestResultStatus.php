<?php

declare(strict_types=1);

namespace App\Enums;

enum TestResultStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Verified = 'verified';
}
