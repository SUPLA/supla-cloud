<?php

namespace SuplaBundle\Utils;

final class NumberUtils {
    private function __construct() {
    }

    public static function maximumDecimalPrecision($number, int $maxPrecision = 2): float {
        return floatval(number_format(floatval($number), $maxPrecision));
    }
}
