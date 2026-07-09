<?php

namespace App\Enums;

enum EnergyTariffType: string {
    case ZONED_STATIC = 'zoned_static';
    case DYNAMIC_15M = 'dynamic_15m';
}
