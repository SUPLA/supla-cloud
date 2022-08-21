<?php
namespace SuplaBundle\Model\ChannelParamsTranslator;

trait FixedRangeParamsTranslator {
    protected function getValueInRange($value, float $min, float $max): float {
        if (is_null($value) || $value === '') {
            return 0;
        }
        if (!is_numeric($value)) {
            return $min;
        }
        return min($max, max($min, floatval($value)));
    }
}
