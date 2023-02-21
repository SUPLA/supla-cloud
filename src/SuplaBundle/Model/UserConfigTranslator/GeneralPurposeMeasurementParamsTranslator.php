<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class GeneralPurposeMeasurementParamsTranslator implements UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return array_merge([
            'initialValue' => NumberUtils::maximumDecimalPrecision($subject->getParam1() / 10000, 4),
            'impulsesPerUnit' => NumberUtils::maximumDecimalPrecision($subject->getParam3() / 10000, 4),
            'unitPrefix' => $subject->getTextParam1() ?: null,
            'unitSuffix' => $subject->getTextParam2() ?: null,
        ], $this->getValuesFromParam2($subject->getParam2()));
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('initialValue', $config)) {
            $subject->setParam1(intval($this->getValueInRange($config['initialValue'], -1000000, 1000000) * 10000));
        }
        $subject->setParam2($this->setValuesToParam2($config, $subject->getParam2()));
        if (array_key_exists('impulsesPerUnit', $config)) {
            $subject->setParam3(intval($this->getValueInRange($config['impulsesPerUnit'], -1000000, 1000000) * 10000));
        }
        if (array_key_exists('unitPrefix', $config)) {
            if (mb_strlen($config['unitPrefix'] ?? '', 'UTF-8') <= 4) {
                $subject->setTextParam1($config['unitPrefix']);
            }
        }
        if (array_key_exists('unitSuffix', $config)) {
            if (mb_strlen($config['unitSuffix'] ?? '', 'UTF-8') <= 4) {
                $subject->setTextParam2($config['unitSuffix']);
            }
        }
    }

    /**
     * 0b000000111: precision (0-5),
     * 0b000001000: whether to store the measurement history,
     * 0b000010000: chart type (0 - linear, 1 - bar),
     * 0b001000000: chart data source type (0 - differential; 1 - standard),
     * 0b100000000: whether to interpolate measurements (only for differential)
     */
    private function getValuesFromParam2(int $value): array {
        return [
            'precision' => $value & 0b000000111,
            'storeMeasurementHistory' => boolval($value & 0b000001000),
            'chartType' => ($value >> 4) & 1,
            'chartDataSourceType' => ($value >> 6) & 1,
            'interpolateMeasurements' => boolval($value & 0b100000000),
        ];
    }

    private function setValuesToParam2(array $config, int $value): int {
        $value &= ~0b010100000; // clear all rubbish :-)
        if (array_key_exists('precision', $config)) {
            $value &= ~0b000000111;
            $value |= max(0, min(intval($config['precision']), 5));
        }
        if (array_key_exists('storeMeasurementHistory', $config)) {
            $value &= ~0b000001000;
            $value |= $config['storeMeasurementHistory'] ? 1 << 3 : 0;
        }
        if (array_key_exists('chartType', $config)) {
            $value &= ~0b000010000;
            $value |= $config['chartType'] ? 1 << 4 : 0;
        }
        if (array_key_exists('chartDataSourceType', $config)) {
            $value &= ~0b001000000;
            $value |= $config['chartDataSourceType'] ? 1 << 6 : 0;
        }
        if (array_key_exists('interpolateMeasurements', $config)) {
            $value &= ~0b100000000;
            $value |= $config['interpolateMeasurements'] ? 1 << 8 : 0;
        }
        return $value;
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
        ]);
    }
}
