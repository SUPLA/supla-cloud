<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Utils\NumberUtils;

class OpeningClosingTimeChannelParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'openingTimeS' => NumberUtils::maximumDecimalPrecision($channel->getParam1() / 10, 1),
            'closingTimeS' => NumberUtils::maximumDecimalPrecision($channel->getParam3() / 10, 1),
            'bottomPosition' => $channel->getParam4(),
            'timeSettingAvailable' => !ChannelFunctionBitsFlags::TIME_SETTING_NOT_AVAILABLE()->isSupported($channel->getFlags()),
            'recalibrateAvailable' => ChannelFunctionBitsFlags::RECALIBRATE_ACTION_AVAILABLE()->isSupported($channel->getFlags()),
            'autoCalibrationAvailable' => ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE()->isSupported($channel->getFlags()),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        $channelConfig = $this->getConfigFromParams($channel);
        $autoCalibrationAvailable = $channelConfig['autoCalibrationAvailable'];
        if ($autoCalibrationAvailable && !($config['openingTimeS'] ?? true)) {
            $config['openingTimeS'] = 0;
            $config['closingTimeS'] = 0;
        }
        if (array_key_exists('openingTimeS', $config)) {
            $channel->setParam1(intval($this->getValueInRange($config['openingTimeS'], 0, 600) * 10));
        }
        if (array_key_exists('closingTimeS', $config)) {
            $channel->setParam3(intval($this->getValueInRange($config['closingTimeS'], 0, 600) * 10));
        }
        if (array_key_exists('bottomPosition', $config)) {
            $channel->setParam4(intval($this->getValueInRange($config['bottomPosition'], 0, 100)));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
        ]);
    }
}
