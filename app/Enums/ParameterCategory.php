<?php

declare(strict_types=1);

namespace App\Enums;

enum ParameterCategory: string
{
    case Mikrobiologi = 'mikrobiologi';
    case Kimia = 'kimia';
    case Fisik = 'fisik';
    case Organoleptik = 'organoleptik';
}
