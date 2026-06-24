<?php

namespace App\Enums;

enum BillingPeriodUnit: string {
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
}
