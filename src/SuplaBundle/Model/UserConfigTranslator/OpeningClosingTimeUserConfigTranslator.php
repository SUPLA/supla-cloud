<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Utils\NumberUtils;

class OpeningClosingTimeUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $config = [
            'openingTimeS' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('openingTimeMs', 0) / 1000, 1),
            'closingTimeS' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('closingTimeMs', 0) / 1000, 1),
            'timeSettingAvailable' => !ChannelFunctionBitsFlags::TIME_SETTING_NOT_AVAILABLE()->isSupported($subject->getFlags()),
            'recalibrateAvailable' => ChannelFunctionBitsFlags::RECALIBRATE_ACTION_AVAILABLE()->isSupported($subject->getFlags()),
            'autoCalibrationAvailable' => ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE()->isSupported($subject->getFlags()),
        ];
        if (($value = $subject->getUserConfigValue('timeMargin')) !== null) {
            $config['timeMargin'] = $value;
        }
        if (($value = $subject->getUserConfigValue('motorUpsideDown')) !== null) {
            $config['motorUpsideDown'] = $value;
        }
        if (($value = $subject->getUserConfigValue('buttonsUpsideDown')) !== null) {
            $config['buttonsUpsideDown'] = $value;
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        $channelConfig = $this->getConfig($subject);
        $autoCalibrationAvailable = $channelConfig['autoCalibrationAvailable'];
        if ($autoCalibrationAvailable && !($config['openingTimeS'] ?? true)) {
            $config['openingTimeS'] = 0;
            $config['closingTimeS'] = 0;
        }
        if (array_key_exists('openingTimeS', $config)) {
            $subject->setUserConfigValue('openingTimeMs', intval($this->getValueInRange($config['openingTimeS'], 0, 600) * 1000));
        }
        if (array_key_exists('closingTimeS', $config)) {
            $subject->setUserConfigValue('closingTimeMs', intval($this->getValueInRange($config['closingTimeS'], 0, 600) * 1000));
        }
        if (array_key_exists('timeMargin', $config) && $subject->getUserConfigValue('timeMargin') !== null) {
            $timeMargin = $config['timeMargin'];
            if (is_numeric($timeMargin)) {
                Assertion::between($timeMargin, 0, 100, null, 'timeMargin');
                $subject->setUserConfigValue('timeMargin', intval($this->getValueInRange($timeMargin, 0, 100)));
            } elseif ($timeMargin !== null) {
                Assertion::inArray($timeMargin, ['DEVICE_SPECIFIC']);
                $subject->setUserConfigValue('timeMargin', $timeMargin);
            }
        }
        if (array_key_exists('motorUpsideDown', $config) && $subject->getUserConfigValue('motorUpsideDown') !== null) {
            $subject->setUserConfigValue('motorUpsideDown', filter_var($config['motorUpsideDown'], FILTER_VALIDATE_BOOLEAN));
        }
        if (array_key_exists('buttonsUpsideDown', $config) && $subject->getUserConfigValue('buttonsUpsideDown') !== null) {
            $subject->setUserConfigValue('buttonsUpsideDown', filter_var($config['buttonsUpsideDown'], FILTER_VALIDATE_BOOLEAN));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
        ]);
    }
}
