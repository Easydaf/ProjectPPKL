<?php

declare(strict_types=1);

namespace App\Enums;

enum BatchStatus: string
{
    case MenungguPenerimaan = 'menunggu_penerimaan';
    case SedangDiuji = 'sedang_diuji';
    case MenungguReview = 'menunggu_review';
    case Lulus = 'lulus';
    case TidakLulus = 'tidak_lulus';
    case Ditahan = 'ditahan';
    case UjiUlang = 'uji_ulang';
    case MenungguRetest = 'menunggu_retest';
    case DalamRetest = 'dalam_retest';
}
