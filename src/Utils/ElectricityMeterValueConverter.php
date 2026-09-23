<?php

namespace App\Utils;

class ElectricityMeterValueConverter {
    public const RAW_ENERGY_PRECISION = 100000;
    public const RAW_POWER_PRECISION = 100000;

    public static function rawEnergyToFloat(int|float|null $value): float {
        return round(((float)$value) / self::RAW_ENERGY_PRECISION, 6);
    }

    public static function rawEnergyToDecimal(int $value): string {
        $scale = strlen((string)self::RAW_ENERGY_PRECISION) - 1;
        $negative = $value < 0;
        $digits = ltrim((string)$value, '-');
        $digits = str_pad($digits, $scale + 1, '0', STR_PAD_LEFT);
        $whole = ltrim(substr($digits, 0, -$scale), '0') ?: '0';
        $fraction = rtrim(substr($digits, -$scale), '0');
        $decimal = $fraction === '' ? $whole : "$whole.$fraction";
        return $negative ? "-$decimal" : $decimal;
    }

    public static function floatToRawEnergy(float $value): int {
        return (int)round($value * self::RAW_ENERGY_PRECISION);
    }
}
