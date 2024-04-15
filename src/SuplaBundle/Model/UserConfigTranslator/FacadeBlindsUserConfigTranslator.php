<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigFacadeBlinds", description="Config for `CONTROLLINGTHEROLLERSHUTTER` function.",
 *   @OA\Property(property="openingTimeS", type="number"),
 *   @OA\Property(property="closingTimeS", type="number"),
 *   @OA\Property(property="tiltingTimeS", type="number"),
 *   @OA\Property(property="timeSettingAvailable", type="boolean"),
 *   @OA\Property(property="recalibrateAvailable", type="boolean"),
 *   @OA\Property(property="autoCalibrationAvailable", type="boolean"),
 *   @OA\Property(property="motorUpsideDown", type="boolean"),
 *   @OA\Property(property="buttonsUpsideDown", type="boolean"),
 *   @OA\Property(property="facadeBlindType", type="string"),
 *   @OA\Property(property="timeMargin", type="integer"),
 *   @OA\Property(property="tilt0Angle", type="integer"),
 *   @OA\Property(property="tilt100Angle", type="integer"),
 * )
 */
class FacadeBlindsUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private const FACADEBLIND_TYPES = [
        1 => 'STANDS_IN_POSITION',
        2 => 'CHANGES_POSITION_WHILE_TILTING',
        3 => 'TILTS_ONLY_WHEN_FULLY_CLOSED',
    ];

    public function getConfig(HasUserConfig $subject): array {
        return [
            'tiltingTimeS' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('tiltingTimeMs', 0) / 1000, 1),
            'motorUpsideDown' => $subject->getUserConfigValue('motorUpsideDown', false),
            'buttonsUpsideDown' => $subject->getUserConfigValue('buttonsUpsideDown', false),
            'timeMargin' => $subject->getUserConfigValue('timeMargin', -1),
            'tilt0Angle' => $subject->getUserConfigValue('tilt0Angle', 0),
            'tilt100Angle' => $subject->getUserConfigValue('tilt100Angle', 0),
            'facadeBlindType' => self::FACADEBLIND_TYPES[$subject->getUserConfigValue('facadeBlindType', 1)],
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('tiltingTimeS', $config)) {
            $subject->setUserConfigValue('tiltingTimeMs', intval($this->getValueInRange($config['tiltingTimeS'], 0, 600) * 1000));
        }
        if (array_key_exists('motorUpsideDown', $config)) {
            $subject->setUserConfigValue('motorUpsideDown', filter_var($config['motorUpsideDown'], FILTER_VALIDATE_BOOLEAN));
        }
        if (array_key_exists('buttonsUpsideDown', $config)) {
            $subject->setUserConfigValue('buttonsUpsideDown', filter_var($config['buttonsUpsideDown'], FILTER_VALIDATE_BOOLEAN));
        }
        if (array_key_exists('tilt0Angle', $config)) {
            $subject->setUserConfigValue('tilt0Angle', intval($this->getValueInRange($config['tilt0Angle'], 0, 180)));
        }
        if (array_key_exists('tilt100Angle', $config)) {
            $subject->setUserConfigValue('tilt100Angle', intval($this->getValueInRange($config['tilt100Angle'], 0, 180)));
        }
        if (array_key_exists('timeMargin', $config)) {
            $subject->setUserConfigValue('timeMargin', intval($this->getValueInRange($config['timeMargin'], -1, 100)));
        }
        if (array_key_exists('facadeBlindType', $config)) {
            Assertion::inArray($config['facadeBlindType'], self::FACADEBLIND_TYPES);
            $subject->setUserConfigValue('facadeBlindType', array_flip(self::FACADEBLIND_TYPES)[$config['facadeBlindType']]);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
        ]);
    }
}
