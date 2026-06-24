<?php

namespace App\Enums;

enum EnergyPriceComponent: int {
    case FORWARD_ACTIVE_ENERGY = 1;
    case DISTRIBUTION_VARIABLE = 2;
    case DISTRIBUTION_FIXED = 3;
    case FEE_VARIABLE = 4;
    case FEE_FIXED = 5;

    public function supportsUnit(EnergyPriceUnit $unit): bool {
        return in_array($unit, $this->supportedUnits(), true);
    }

    /**
     * @return EnergyPriceUnit[]
     */
    public function supportedUnits(): array {
        return match ($this) {
            self::FORWARD_ACTIVE_ENERGY, self::DISTRIBUTION_VARIABLE => [EnergyPriceUnit::KWH],
            self::DISTRIBUTION_FIXED, self::FEE_VARIABLE, self::FEE_FIXED => [
                EnergyPriceUnit::DAY,
                EnergyPriceUnit::WEEK,
                EnergyPriceUnit::MONTH,
                EnergyPriceUnit::PERIOD,
            ],
        };
    }
}
