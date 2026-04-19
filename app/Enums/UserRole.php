<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case ManagerProduksi = 'manager_produksi';
    case AnalisLab = 'analis_lab';
    case QcManager = 'qc_manager';
    case StaffGudang = 'staff_gudang';
    case RaOfficer = 'ra_officer';
    case AdminSistem = 'admin_sistem';
}
