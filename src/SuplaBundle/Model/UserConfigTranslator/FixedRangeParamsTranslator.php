<?php
namespace SuplaBundle\Model\UserConfigTranslator;

trait FixedRangeParamsTranslator {
    protected function getValueInRange($value, float $min, float $max, float $default = null): float {
        if (is_null($value) || $value === '') {
            return $default ?? 0;
        }
        if (!is_numeric($value)) {
            return $default ?? $min;
        }
        return min($max, max($min, floatval($value)));
    }
}
