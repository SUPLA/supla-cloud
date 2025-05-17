<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\RollerShutterStateBits;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Supla\SuplaServerIsDownException;

/**
 * @OA\Schema(schema="ChannelStateFacadeBlind",
 *      description="State of `CONTROLLINGTHEFACADEBLIND`",
 *      @OA\Property(property="connected", type="boolean"),
 *      @OA\Property(property="shut", type="integer", maximum=100, minimum=0),
 *      @OA\Property(property="tiltPercent", type="integer", maximum=100, minimum=0),
 *      @OA\Property(property="tiltAngle", type="integer"),
 *      @OA\Property(property="isCalibrating", type="boolean"),
 *      @OA\Property(property="notCalibrated", type="boolean"),
 *      @OA\Property(property="calibrationFailed", type="boolean"),
 *      @OA\Property(property="calibrationLost", type="boolean"),
 *      @OA\Property(property="motorProblem", type="boolean"),
 *  )
 */
class FacadeBlindChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        // VALUE:%shutPercent,%tiltPercent,%tiltAngle,%flags
        $value = $this->suplaServer->getRawValue('FACADE-BLIND', $channel);
        $value = rtrim($value);
        $values = explode(',', substr($value, strlen('VALUE:')));
        if (count($values) !== 4) {
            throw new SuplaServerIsDownException('Invalid response for FACADE-BLIND: ' . $value);
        }
        [$position, $tiltPercent, $tiltAngle, $flags] = array_map('intval', $values);
        return [
            'notCalibrated' => $position === -1,
            'isCalibrating' => RollerShutterStateBits::CALIBRATION_IN_PROGRESS()->isOn($flags),
            'calibrationError' => RollerShutterStateBits::CALIBRATION_FAILED()->isOn($flags),
            'calibrationLost' => RollerShutterStateBits::CALIBRATION_LOST()->isOn($flags),
            'motorProblem' => RollerShutterStateBits::MOTOR_PROBLEM()->isOn($flags),
            'shut' => min(100, max(0, $position)),
            'tiltPercent' => $tiltPercent,
            'tiltAngle' => $tiltAngle,
        ];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEFACADEBLIND(),
            ChannelFunction::VERTICAL_BLIND(),
        ];
    }
}
