<?php

namespace App\Utils;

class ElectricityMeterValueConverter {
    public const RAW_ENERGY_PRECISION = 100000;

    public static function rawEnergyToFloat(int | float | null $value): float {
        return round(((float)$value) / self::RAW_ENERGY_PRECISION, 6);
    }

    public static function floatToRawEnergy(float $value): int {
        return (int)round($value * self::RAW_ENERGY_PRECISION);
    }
}
