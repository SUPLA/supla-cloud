<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
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
class GoogleHomeConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_PIN_LENGTH = 4;
    private const MAX_PIN_LENGTH = 8;

    /** @var string */
    private $secret;

    public function __construct(string $secret) {
        $this->secret = $secret;
    }

    public function getConfig(HasUserConfig $subject): array {
        $googleHomeSettings = $subject->getUserConfigValue('googleHome', []);
        $settings = [
            'googleHomeDisabled' => $googleHomeSettings['googleHomeDisabled'] ?? false,
            'needsUserConfirmation' => $googleHomeSettings['needsUserConfirmation'] ?? false,
            'pin' => $googleHomeSettings['pin'] ?? null,
            'pinSet' => !!($googleHomeSettings['pin'] ?? false),
        ];
        return ['googleHome' => $settings];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('googleHome', $config) && is_array($config['googleHome'])) {
            $finalSettings = $subject->getUserConfigValue('googleHome', []);
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
                    $finalSettings['pin'] = $subject->getUser()->hashValue($pin);
                } else {
                    $finalSettings['pin'] = null;
                }
            }
            $subject->setUserConfigValue('googleHome', $finalSettings);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        // https://github.com/ACSOFTWARE/supla-aws-lambda/blob/master/google/channels.js#L92
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::LIGHTSWITCH,
            ChannelFunction::STAIRCASETIMER,
            ChannelFunction::POWERSWITCH,
            ChannelFunction::RGBLIGHTING,
            ChannelFunction::DIMMER,
            ChannelFunction::DIMMERANDRGBLIGHTING,
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::SCENE,
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
            ChannelFunction::CONTAINER_LEVEL_SENSOR,
            ChannelFunction::FLOOD_SENSOR,
        ]);
    }
}
