<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigGoogleHomeSettings", description="Config for Google Home integration.",
 *   @OA\Property(property="googleHome",
 *     @OA\Property(property="googleHomeDisabled", type="boolean"),
 *     @OA\Property(property="needsUserConfirmation", type="boolean"),
 *     @OA\Property(property="pin", type="string", minLength=4, maxLength=8),
 *     @OA\Property(property="pinSet", type="boolean", readOnly=true),
 *   ),
 * )
 */
class GoogleHomeSettingsParamsTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_PIN_LENGTH = 4;
    private const MAX_PIN_LENGTH = 8;

    /** @var string */
    private $secret;

    public function __construct(string $secret) {
        $this->secret = $secret;
    }

    public function getConfigFromParams(IODeviceChannel $channel): array {
        $googleHomeSettings = $channel->getUserConfigValue('googleHome', []);
        $settings = [
            'googleHomeDisabled' => $googleHomeSettings['googleHomeDisabled'] ?? false,
            'needsUserConfirmation' => $googleHomeSettings['needsUserConfirmation'] ?? false,
            'pin' => $googleHomeSettings['pin'] ?? null,
            'pinSet' => !!($googleHomeSettings['pin'] ?? false),
        ];
        return ['googleHome' => $settings];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('googleHome', $config) && is_array($config['googleHome'])) {
            $finalSettings = $channel->getUserConfigValue('googleHome', []);
            $googleHomeSettings = $config['googleHome'];
            Assertion::isArray($googleHomeSettings);
            if (array_key_exists('googleHomeDisabled', $googleHomeSettings)) {
                $finalSettings['googleHomeDisabled'] = filter_var($googleHomeSettings['googleHomeDisabled'], FILTER_VALIDATE_BOOLEAN);
            }
            if (array_key_exists('needsUserConfirmation', $googleHomeSettings)) {
                $needsUserConfirmation = $googleHomeSettings['needsUserConfirmation'];
                $finalSettings['needsUserConfirmation'] = filter_var($needsUserConfirmation, FILTER_VALIDATE_BOOLEAN);
            }
            if (array_key_exists('pin', $googleHomeSettings) || ($finalSettings['needsUserConfirmation'] ?? false)) {
                $pin = $googleHomeSettings['pin'] ?? null;
                if ($pin) {
                    Assert::that($pin)
                        ->string()
                        ->betweenLength(self::MIN_PIN_LENGTH, self::MAX_PIN_LENGTH)
                        ->numeric();
                    $finalSettings['pin'] = $channel->getUser()->hashValue($pin);
                } else {
                    $finalSettings['pin'] = null;
                }
            }
            $channel->setUserConfigValue('googleHome', $finalSettings);
        }
    }

    private function canSetUserConfirmation(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
        ]);
    }

    public function supports(IODeviceChannel $channel): bool {
        // https://github.com/ACSOFTWARE/supla-aws-lambda/blob/master/google/channels.js#L92
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::LIGHTSWITCH,
            ChannelFunction::STAIRCASETIMER,
            ChannelFunction::POWERSWITCH,
            ChannelFunction::RGBLIGHTING,
            ChannelFunction::DIMMER,
            ChannelFunction::DIMMERANDRGBLIGHTING,
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
        ]);
    }
}
