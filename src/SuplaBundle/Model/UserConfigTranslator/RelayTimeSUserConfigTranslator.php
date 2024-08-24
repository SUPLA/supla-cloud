<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigStaircaseTimer", description="Config for `STAIRCASETIMER`.",
 *   @OA\Property(property="timeSettingAvailable", type="boolean", readOnly=true),
 *   @OA\Property(property="relayTimeS", type="integer", minimum=0, maximum=7200),
 *   @OA\Property(property="relatedMeterChannelId", type="integer"),
 * )
 */
class RelayTimeSUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'relayTimeS' => NumberUtils::maximumDecimalPrecision($subject->getParam1() / 10, 1),
            'timeSettingAvailable' => !ChannelFunctionBitsFlags::TIME_SETTING_NOT_AVAILABLE()->isSupported($subject->getFlags()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('relayTimeS', $config)) {
            $subject->setParam1(intval($this->getValueInRange($config['relayTimeS'], 0, 7200) * 10));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::STAIRCASETIMER,
        ]);
    }
}
