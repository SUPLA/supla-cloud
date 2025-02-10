<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigInvertedLogic", description="Config for sensors.",
 *   @OA\Property(property="invertedLogic", type="boolean"),
 * )
 */
class InvertedLogicParamTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'invertedLogic' => $subject->getUserConfigValue('invertedLogic', false),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('invertedLogic', $config)) {
            $subject->setUserConfigValue('invertedLogic', boolval($config['invertedLogic']));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::MAILSENSOR,
            ChannelFunction::NOLIQUIDSENSOR,
            ChannelFunction::OPENINGSENSOR_DOOR,
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
            ChannelFunction::OPENINGSENSOR_GATE,
            ChannelFunction::OPENINGSENSOR_GATEWAY,
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER,
            ChannelFunction::OPENINGSENSOR_ROOFWINDOW,
            ChannelFunction::OPENINGSENSOR_WINDOW,
            ChannelFunction::HOTELCARDSENSOR,
            ChannelFunction::ALARM_ARMAMENT_SENSOR,
            ChannelFunction::CONTAINER_LEVEL_SENSOR,
            ChannelFunction::FLOOD_SENSOR,
        ]);
    }
}
