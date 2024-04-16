<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use InvalidArgumentException;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Supla\SuplaServerIsDownException;

/**
 * @OA\Schema(schema="ChannelState", description="The channel's state. The value and the format depends on the channel's function. Read more on [Github Wiki](https://github.com/SUPLA/supla-cloud/wiki/Channel-Functions-states).",
 *     externalDocs={"description": "Github Wiki", "url":"https://github.com/SUPLA/supla-cloud/wiki/Channel-Functions-states"},
 *   oneOf={
 *     @OA\Schema(ref="#/components/schemas/ChannelStateConnected"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateBrightness"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateColor"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateColorAndBrightness"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateDepth"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateDistance"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateDouble"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateElectricityMeter"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateSensor"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateSensorPartial"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateHumidity"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateHumidityAndTemperature"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateHvac"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateTemperature"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateImpulseCounter"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateOnOff"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateRelay"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateRollerShutter"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateValve"),
 *     @OA\Schema(ref="#/components/schemas/SceneState"),
 *     @OA\Schema(ref="#/components/schemas/ChannelStateFacadeBlind"),
 *   }
 * )
 */
class ChannelStateGetter {
    /** @var SingleChannelStateGetter[] */
    private $stateGetters = [];
    /** @var SceneStateGetter */
    private $sceneStateGetter;
    /** @var ScheduleStateGetter */
    private $scheduleStateGetter;

    public function __construct($stateGetters, SceneStateGetter $sceneStateGetter, ScheduleStateGetter $scheduleStateGetter) {
        $this->stateGetters = $stateGetters;
        $this->sceneStateGetter = $sceneStateGetter;
        $this->scheduleStateGetter = $scheduleStateGetter;
    }

    public function getState(ActionableSubject $subject): array {
        if ($subject instanceof IODeviceChannel) {
            $state = [];
            try {
                foreach ($this->stateGetters as $stateGetter) {
                    if (in_array($subject->getFunction(), $stateGetter->supportedFunctions())) {
                        $state = array_merge($state, $stateGetter->getState($subject));
                    }
                }
            } catch (SuplaServerIsDownException $e) {
                $state = ['connected' => false];
            }
            return $state;
        } elseif ($subject instanceof IODeviceChannelGroup) {
            return $this->getStateForChannelGroup($subject);
        } elseif ($subject instanceof Scene) {
            return $this->sceneStateGetter->getState($subject);
        } elseif ($subject instanceof Schedule) {
            return $this->scheduleStateGetter->getState($subject);
        } else {
            throw new InvalidArgumentException('Could not get state for entity ' . get_class($subject));
        }
    }

    public function getStateForChannelGroup(IODeviceChannelGroup $group): array {
        $state = [];
        foreach ($group->getChannels() as $channel) {
            $state[$channel->getId()] = $this->getState($channel);
        }
        return $state;
    }
}
