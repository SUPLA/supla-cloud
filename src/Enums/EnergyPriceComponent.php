<?php

namespace App\Enums;

enum EnergyPriceComponent: int {
    case FORWARD_ACTIVE_ENERGY = 1;
    case DISTRIBUTION_VARIABLE = 2;
    case DISTRIBUTION_FIXED = 3;
    case FEE_VARIABLE = 4;
    case FEE_FIXED = 5;
}
