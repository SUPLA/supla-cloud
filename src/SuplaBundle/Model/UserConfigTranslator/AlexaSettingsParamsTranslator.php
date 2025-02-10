<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigAlexaSettings", description="Config for Alexa integration.",
 *   @OA\Property(property="alexa",
 *     @OA\Property(property="alexaDisabled", type="boolean"),
 *   ),
 * )
 */
class AlexaSettingsParamsTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_PIN_LENGTH = 4;
    private const MAX_PIN_LENGTH = 8;

    /** @var string */
    private $secret;

    public function __construct(string $secret) {
        $this->secret = $secret;
    }

    public function getConfig(HasUserConfig $subject): array {
        $alexaSettings = $subject->getUserConfigValue('alexa', []);
        return [
            'alexa' => [
                'alexaDisabled' => $alexaSettings['alexaDisabled'] ?? false,
            ],
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('alexa', $config) && is_array($config['alexa'])) {
            $finalSettings = $subject->getUserConfigValue('alexa', []);
            $alexaSettings = $config['alexa'];
            Assertion::isArray($alexaSettings);
            if (array_key_exists('alexaDisabled', $alexaSettings)) {
                $finalSettings['alexaDisabled'] = filter_var($alexaSettings['alexaDisabled'], FILTER_VALIDATE_BOOLEAN);
            }
            $subject->setUserConfigValue('alexa', $finalSettings);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        // https://github.com/ACSOFTWARE/supla-aws-lambda/blob/master/alexa/channels.js#L283
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::LIGHTSWITCH,
            ChannelFunction::STAIRCASETIMER,
            ChannelFunction::POWERSWITCH,
            ChannelFunction::RGBLIGHTING,
            ChannelFunction::DIMMER,
            ChannelFunction::DIMMERANDRGBLIGHTING,
            ChannelFunction::OPENINGSENSOR_GATE,
            ChannelFunction::OPENINGSENSOR_GATEWAY,
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
            ChannelFunction::OPENINGSENSOR_DOOR,
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER,
            ChannelFunction::OPENINGSENSOR_WINDOW,
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
            ChannelFunction::SCENE,
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
            ChannelFunction::CONTAINER_LEVEL_SENSOR,
            ChannelFunction::FLOOD_SENSOR,
        ]);
    }
}
