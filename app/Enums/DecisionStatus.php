<?php

declare(strict_types=1);

namespace App\Enums;

enum DecisionStatus: string
{
    case Lulus = 'lulus';
    case TidakLulus = 'tidak_lulus';
    case Ditahan = 'ditahan';
    case UjiUlang = 'uji_ulang';
}
