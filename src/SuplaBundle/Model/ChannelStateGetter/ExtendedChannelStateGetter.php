<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFlags;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class ExtendedChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function __construct(private readonly EntityManagerInterface $em) {
    }

    public function getState(IODeviceChannel $channel): array {
        if (ChannelFlags::HAS_EXTENDED_CHANNEL_STATE()->isOn($channel->getFlags())) {
            $this->suplaServer->channelAction($channel, 'UPDATE-CHANNEL-STATE');
            $this->em->refresh($channel);
            return ['extendedState' => $channel->getLastKnownChannelState()];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return ChannelFunction::values();
    }
}
