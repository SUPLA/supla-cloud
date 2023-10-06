<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Utils\NumberUtils;

class OpeningClosingTimeUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'openingTimeS' => NumberUtils::maximumDecimalPrecision($subject->getParam1() / 10, 1),
            'closingTimeS' => NumberUtils::maximumDecimalPrecision($subject->getParam3() / 10, 1),
            'bottomPosition' => $subject->getParam4(),
            'timeSettingAvailable' => !ChannelFunctionBitsFlags::TIME_SETTING_NOT_AVAILABLE()->isSupported($subject->getFlags()),
            'recalibrateAvailable' => ChannelFunctionBitsFlags::RECALIBRATE_ACTION_AVAILABLE()->isSupported($subject->getFlags()),
            'autoCalibrationAvailable' => ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE()->isSupported($subject->getFlags()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        $channelConfig = $this->getConfig($subject);
        $autoCalibrationAvailable = $channelConfig['autoCalibrationAvailable'];
        if ($autoCalibrationAvailable && !($config['openingTimeS'] ?? true)) {
            $config['openingTimeS'] = 0;
            $config['closingTimeS'] = 0;
        }
        if (array_key_exists('openingTimeS', $config)) {
            $subject->setParam1(intval($this->getValueInRange($config['openingTimeS'], 0, 600) * 10));
        }
        if (array_key_exists('closingTimeS', $config)) {
            $subject->setParam3(intval($this->getValueInRange($config['closingTimeS'], 0, 600) * 10));
        }
        if (array_key_exists('bottomPosition', $config)) {
            $subject->setParam4(intval($this->getValueInRange($config['bottomPosition'], 0, 100)));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
        ]);
    }
}
