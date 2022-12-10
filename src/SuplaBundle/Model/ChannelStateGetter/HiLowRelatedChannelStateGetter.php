<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Supla\SuplaServerAware;

class HiLowRelatedChannelStateGetter implements SingleChannelStateGetter {
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
        $sensorId = $channel->getParam2();
        if ($sensorId) {
            $sensor = $this->channelRepository->find($sensorId);
            if ($sensor) {
                return $this->hiLowChannelStateGetter->getState($sensor);
            }
        }
        return [];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
            ChannelFunction::CONTROLLINGTHEROOFWINDOW(),
            ChannelFunction::CONTROLLINGTHEDOORLOCK(),
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
            ChannelFunction::CONTROLLINGTHEGATE(),
        ];
    }
}
