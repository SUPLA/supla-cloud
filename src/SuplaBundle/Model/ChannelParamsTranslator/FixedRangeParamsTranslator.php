<?php
namespace SuplaBundle\Model\ChannelParamsTranslator;

trait FixedRangeParamsTranslator {
    protected function getValueInRange($value, int $min, int $max): float {
        if (is_null($value)) {
            return 0;
        }
        if (!is_numeric($value)) {
            return $min;
        }
        return min($max, max($min, floatval($value)));
    }
}
