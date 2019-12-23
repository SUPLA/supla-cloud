<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class GeneralPurposeMeasurementParamsTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return array_merge([
            'measurementMultiplier' => NumberUtils::maximumDecimalPrecision($channel->getParam1() / 10000, 4),
            'measurementAdjustment' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 10000, 4),
            'unitPrefix' => $channel->getTextParam1(),
            'unitSuffix' => $channel->getTextParam2(),
        ], $this->getValuesFromParam3($channel->getParam3()));
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['measurementMultiplier'])) {
            $channel->setParam1(intval($config['measurementMultiplier'] * 10000));
        }
        if (isset($config['measurementAdjustment'])) {
            $channel->setParam2(intval($config['measurementAdjustment'] * 10000));
        }
        $channel->setParam3($this->setValuesToParam3($config, $channel->getParam3()));
        $channel->setTextParam1($config['unitPrefix'] ?? $channel->getTextParam1());
        $channel->setTextParam2($config['unitSuffix'] ?? $channel->getTextParam2());
    }

    /**
     * 0b000000111: precision (0-5),
     * 0b000001000: whether to store the measurement history,
     * 0b000010000: chart presentation (0 - linear, 1 - bar),
     * 0b001000000: chart type (0 - differential; 1 - standard),
     * 0b100000000: whether to interpolate measurements (only for differential)
     */
    private function getValuesFromParam3(int $value): array {
        return [
            'precision' => $value & 0b000000111,
            'storeMeasurementHistory' => boolval($value & 0b000001000),
            'chartPresentation' => ($value >> 4) & 1,
            'chartType' => ($value >> 6) & 1,
            'interpolateMeasurements' => boolval($value & 0b100000000),
        ];
    }

    private function setValuesToParam3(array $config, int $value): int {
        if (isset($config['precision'])) {
            $value &= ~0b000000111;
            $value |= max(0, min($config['precision'], 5));
        }
        if (isset($config['storeMeasurementHistory'])) {
            $value &= ~0b000001000;
            $value |= $config['storeMeasurementHistory'] ? 1 << 3 : 0;
        }
        if (isset($config['chartPresentation'])) {
            $value &= ~0b000010000;
            $value |= $config['chartPresentation'] ? 1 << 4 : 0;
        }
        if (isset($config['chartType'])) {
            $value &= ~0b001000000;
            $value |= $config['chartType'] ? 1 << 6 : 0;
        }
        if (isset($config['interpolateMeasurements'])) {
            $value &= ~0b100000000;
            $value |= $config['interpolateMeasurements'] ? 1 << 8 : 0;
        }
        return $value;
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
        ]);
    }
}
