<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
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
 *  )
 */
class FacadeBlindChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        // VALUE:%shutPercent,%tiltPercent,%tiltAngle
        $value = $this->suplaServer->getRawValue('FACADE-BLIND', $channel);
        $value = rtrim($value);
        $values = explode(',', substr($value, strlen('VALUE:')));
        if (count($values) !== 3) {
            throw new SuplaServerIsDownException();
        }
        [$shutPercent, $tiltPercent, $tiltAngle] = array_map('intval', $values);
        return [
            'isCalibrating' => $shutPercent == -1,
            'notCalibrated' => $shutPercent == -1,
            'shut' => $shutPercent,
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
