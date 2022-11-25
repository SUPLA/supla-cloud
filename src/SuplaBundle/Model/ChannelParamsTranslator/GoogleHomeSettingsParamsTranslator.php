<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\IODeviceChannel;

/**
 * @OA\Schema(schema="ChannelConfigGoogleHomeSettings", description="Config for Google Home integration.",
 *   @OA\Property(property="googleHome",
 *     @OA\Property(property="googleHomeEnabled", type="boolean"),
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
        return [
            'googleHome' => [
                'googleHomeDisabled' => $googleHomeSettings['googleHomeDisabled'] ?? false,
                'needsUserConfirmation' => $googleHomeSettings['needsUserConfirmation'] ?? false,
                'pin' => $googleHomeSettings['pin'] ?? null,
                'pinSet' => !!($googleHomeSettings['pin'] ?? false),
            ],
        ];
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
                $finalSettings['needsUserConfirmation'] = filter_var($googleHomeSettings['needsUserConfirmation'], FILTER_VALIDATE_BOOLEAN);
            }
            if (array_key_exists('pin', $googleHomeSettings) && $googleHomeSettings['pin'] !== '*') {
                $pin = $googleHomeSettings['pin'];
                if ($pin) {
                    Assert::that($pin)
                        ->string()
                        ->betweenLength(self::MIN_PIN_LENGTH, self::MAX_PIN_LENGTH)
                        ->numeric();
                    $finalSettings['pin'] = sha1($this->secret . $pin);
                } else {
                    $finalSettings['pin'] = null;
                }
            }
            $channel->setUserConfigValue('googleHome', $finalSettings);
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getFunction()->isOutput();
    }
}
