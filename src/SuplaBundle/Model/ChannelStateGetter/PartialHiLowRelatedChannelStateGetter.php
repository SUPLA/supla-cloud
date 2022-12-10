<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateSensorPartial",
 *     description="State of channels with paired partial sensors, e.g. `CONTROLLINGTHEGATE`, `CONTROLLINGTHEGARAGEDOOR`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="hi", type="boolean"),
 *     @OA\Property(property="partial_hi", type="boolean"),
 * )
 */
class PartialHiLowRelatedChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var HiLowChannelStateGetter */
    private $hiLowChannelStateGetter;

    public function __construct(IODeviceChannelRepository $channelRepository, HiLowChannelStateGetter $hiLowChannelStateGetter) {
        $this->channelRepository = $channelRepository;
        $this->hiLowChannelStateGetter = $hiLowChannelStateGetter;
    }

    public function getState(IODeviceChannel $channel): array {
        $sensorId = $channel->getParam3();
        if ($sensorId) {
            $sensor = $this->channelRepository->find($sensorId);
            if ($sensor) {
                $state = $this->hiLowChannelStateGetter->getState($sensor);
                return ['partial_hi' => $state['hi']];
            }
        }
        return [];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGATE(),
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
        ];
    }
}
