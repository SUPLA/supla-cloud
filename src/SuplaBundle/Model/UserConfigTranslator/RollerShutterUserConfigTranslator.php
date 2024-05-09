<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ChannelConfigRollerShutter", description="Config for `CONTROLLINGTHEROLLERSHUTTER` function.",
 *   @OA\Property(property="bottomPosition", type="integer"),
 *   @OA\Property(property="openingTimeS", type="number"),
 *   @OA\Property(property="closingTimeS", type="number"),
 *   @OA\Property(property="timeSettingAvailable", type="boolean"),
 *   @OA\Property(property="recalibrateAvailable", type="boolean"),
 *   @OA\Property(property="autoCalibrationAvailable", type="boolean"),
 *   @OA\Property(property="motorUpsideDown", type="boolean"),
 *   @OA\Property(property="buttonsUpsideDown", type="boolean"),
 *   @OA\Property(property="timeMargin", oneOf={
 *     @OA\Schema(type="integer", minimum=0, maximum=100),
 *     @OA\Schema(type="string", enum={"DEVICE_SPECIFIC"}),
 *   }),
 * )
 */
class RollerShutterUserConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'bottomPosition' => $subject->getParam4(),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('bottomPosition', $config)) {
            $subject->setParam4(intval($this->getValueInRange($config['bottomPosition'], 0, 100)));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ]);
    }
}
