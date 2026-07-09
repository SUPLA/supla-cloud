<?php

namespace App\Enums;

use App\Entity\MeasurementLogs\EnergyPriceLogItem;

enum EnergyTariffDynamicPriceSource: string {
    case RCE = 'rce';
    case FIXING1 = 'fixing1';
    case FIXING2 = 'fixing2';

    public function extractValue(EnergyPriceLogItem $log): ?float {
        return match ($this) {
            self::RCE => $log->getRce(),
            self::FIXING1 => $log->getFixing1(),
            self::FIXING2 => $log->getFixing2(),
        };
    }
}
