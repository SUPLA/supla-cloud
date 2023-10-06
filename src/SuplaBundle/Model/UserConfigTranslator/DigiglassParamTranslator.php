<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class DigiglassParamTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    const MINUTES_IN_DAY = 1440;
    const MAX_SECTIONS = 7;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'sectionsCount' => $subject->getParam1(),
            'regenerationTimeStart' => $subject->getParam2(),
        ];
    }

    /** @param IODeviceChannel $subject */
    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('sectionsCount', $config)) {
            $subject->setParam1(intval($this->getValueInRange($config['sectionsCount'], 1, self::MAX_SECTIONS)));
        }
        if (array_key_exists('regenerationTimeStart', $config)) {
            $subject->setParam2(intval($this->getValueInRange($config['regenerationTimeStart'], 0, self::MINUTES_IN_DAY - 1)));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::DIGIGLASS_VERTICAL,
            ChannelFunction::DIGIGLASS_HORIZONTAL,
        ]);
    }
}
