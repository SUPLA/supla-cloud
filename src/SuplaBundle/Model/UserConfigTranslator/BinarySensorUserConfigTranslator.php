<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigInvertedLogic", description="Config for sensors.",
 *   @OA\Property(property="invertedLogic", type="boolean"),
 *   @OA\Property(property="filteringTimeMs", type="integer", minimum=30, maximum=3000),
 *   @OA\Property(property="timeoutS", type="number", minimum=0.1, maximum=3600),
 *   @OA\Property(property="sensitivity", type="integer", minimum=0, maximum=100),
 * )
 */
class BinarySensorUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $config = [
            'invertedLogic' => $subject->getUserConfigValue('invertedLogic', false),
        ];
        if ($subject->getUserConfigValue('filteringTimeMs') > 0) {
            $config['filteringTimeMs'] = $subject->getUserConfigValue('filteringTimeMs');
        }
        if ($subject->getUserConfigValue('timeout') !== null) {
            $config['timeoutS'] = NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('timeout') / 10, 1);
        }
        if ($subject->getUserConfigValue('sensitivity') !== null) {
            $config['sensitivity'] = $subject->getUserConfigValue('sensitivity');
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('invertedLogic', $config)) {
            $subject->setUserConfigValue('invertedLogic', boolval($config['invertedLogic']));
        }
        if (array_key_exists('filteringTimeMs', $config) && is_numeric($config['filteringTimeMs'])) {
            Assertion::notNull($subject->getUserConfigValue('filteringTimeMs'));
            $subject->setUserConfigValue('filteringTimeMs', intval($this->getValueInRange($config['filteringTimeMs'], 30, 3000)));
        }
        if (array_key_exists('timeoutS', $config) && is_numeric($config['timeoutS'])) {
            Assertion::notNull($subject->getUserConfigValue('timeout'));
            $subject->setUserConfigValue('timeout', intval($this->getValueInRange($config['timeoutS'] * 10, 1, 36000)));
        }
        if (array_key_exists('sensitivity', $config) && is_numeric($config['sensitivity'])) {
            Assertion::notNull($subject->getUserConfigValue('sensitivity'));
            Assertion::between($config['sensitivity'], 0, 100, 'Sensitivity must be between 0 and 100.');
            $subject->setUserConfigValue('sensitivity', intval($config['sensitivity']));
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
            ChannelFunction::MOTION_SENSOR,
            ChannelFunction::BINARY_SENSOR,
        ]);
    }
}
