<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\RollerShutterStateBits;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Supla\SuplaServerIsDownException;

/**
 * @OA\Schema(schema="ChannelStateRollerShutter",
 *     description="State of `CONTROLLINGTHEROLLERSHUTTER`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="isCalibrating", type="boolean"),
 *     @OA\Property(property="notCalibrated", type="boolean"),
 *     @OA\Property(property="calibrationFailed", type="boolean"),
 *     @OA\Property(property="calibrationLost", type="boolean"),
 *     @OA\Property(property="motorProblem", type="boolean"),
 *     @OA\Property(property="shut", type="integer", maximum=100, minimum=0),
 * )
 */
class RollerShutterChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        // VALUE:%position,%flags
        $value = $this->suplaServer->getRawValue('ROLLERSHUTTER', $channel);
        $value = rtrim($value);
        $values = explode(',', substr($value, strlen('VALUE:')));
        if (count($values) !== 2) {
            throw new SuplaServerIsDownException();
        }
        [$position, $flags] = array_map('intval', $values);
        return [
            'notCalibrated' => $position === -1,
            'isCalibrating' => RollerShutterStateBits::CALIBRATION_IN_PROGRESS()->isOn($flags),
            'calibrationError' => RollerShutterStateBits::CALIBRATION_FAILED()->isOn($flags),
            'calibrationLost' => RollerShutterStateBits::CALIBRATION_LOST()->isOn($flags),
            'motorProblem' => RollerShutterStateBits::MOTOR_PROBLEM()->isOn($flags),
            'shut' => min(100, max(0, $position)),
        ];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
            ChannelFunction::TERRACE_AWNING(),
            ChannelFunction::PROJECTOR_SCREEN(),
            ChannelFunction::CURTAIN(),
            ChannelFunction::ROLLER_GARAGE_DOOR(),
            ChannelFunction::CONTROLLINGTHEFACADEBLIND(),
            ChannelFunction::VERTICAL_BLIND(),
        ];
    }
}
