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
 *   @OA\Property(property="tiltControlType", type="string"),
 *   @OA\Property(property="timeMargin", oneOf={
 *       @OA\Schema(type="integer", minimum=0, maximum=100),
 *       @OA\Schema(type="string", enum={"DEVICE_SPECIFIC"}),
 *   }),
 *   @OA\Property(property="tilt0Angle", type="integer"),
 *   @OA\Property(property="tilt100Angle", type="integer"),
 * )
 */
class FacadeBlindsUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private const FACADEBLIND_TYPES = [
        'UNKNOWN',
        'STANDS_IN_POSITION_WHILE_TILTING',
        'CHANGES_POSITION_WHILE_TILTING',
        'TILTS_ONLY_WHEN_FULLY_CLOSED',
    ];

    public function getConfig(HasUserConfig $subject): array {
        $subjectConfig = $subject->getUserConfig();
        if (array_key_exists('tiltControlType', $subjectConfig)) {
            $config = [
                'tiltingTimeS' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('tiltingTimeMs', 0) / 1000, 1),
                'tilt0Angle' => $subject->getUserConfigValue('tilt0Angle', 0),
                'tilt100Angle' => $subject->getUserConfigValue('tilt100Angle', 0),
                'tiltControlType' => $subject->getUserConfigValue('tiltControlType', self::FACADEBLIND_TYPES[0]),
            ];
            return $config;
        } else {
            return ['waitingForConfigInit' => true];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('tiltingTimeS', $config)) {
            $subject->setUserConfigValue('tiltingTimeMs', intval($this->getValueInRange($config['tiltingTimeS'], 0, 600) * 1000));
        }
        if (array_key_exists('tilt0Angle', $config)) {
            $subject->setUserConfigValue('tilt0Angle', intval($this->getValueInRange($config['tilt0Angle'], 0, 180)));
        }
        if (array_key_exists('tilt100Angle', $config)) {
            $subject->setUserConfigValue('tilt100Angle', intval($this->getValueInRange($config['tilt100Angle'], 0, 180)));
        }
        if (array_key_exists('tiltControlType', $config)) {
            if (!$config['tiltControlType']) {
                $config['tiltControlType'] = self::FACADEBLIND_TYPES[0];
            }
            Assertion::inArray($config['tiltControlType'], self::FACADEBLIND_TYPES);
            $subject->setUserConfigValue('tiltControlType', $config['tiltControlType']);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::VERTICAL_BLIND,
        ]);
    }
}
