<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigAlexaSettings", description="Config for Alexa integration.",
 *   @OA\Property(property="alexa",
 *     @OA\Property(property="alexaDisabled", type="boolean"),
 *   ),
 * )
 */
class AlexaSettingsParamsTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_PIN_LENGTH = 4;
    private const MAX_PIN_LENGTH = 8;

    /** @var string */
    private $secret;

    public function __construct(string $secret) {
        $this->secret = $secret;
    }

    public function getConfigFromParams(IODeviceChannel $channel): array {
        $alexaSettings = $channel->getUserConfigValue('alexa', []);
        return [
            'alexa' => [
                'alexaDisabled' => $alexaSettings['alexaDisabled'] ?? false,
            ],
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('alexa', $config) && is_array($config['alexa'])) {
            $finalSettings = $channel->getUserConfigValue('alexa', []);
            $alexaSettings = $config['alexa'];
            Assertion::isArray($alexaSettings);
            if (array_key_exists('alexaDisabled', $alexaSettings)) {
                $finalSettings['alexaDisabled'] = filter_var($alexaSettings['alexaDisabled'], FILTER_VALIDATE_BOOLEAN);
            }
            $channel->setUserConfigValue('alexa', $finalSettings);
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        // https://github.com/ACSOFTWARE/supla-aws-lambda/blob/master/alexa/channels.js#L283
        return in_array($channel->getFunction()->getId(), [
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
        ]);
    }
}
