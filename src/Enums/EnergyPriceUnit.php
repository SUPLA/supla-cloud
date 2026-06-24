<?php

namespace App\Enums;

enum EnergyPriceUnit: string {
    case KWH = 'kWh';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case PERIOD = 'period';
}
