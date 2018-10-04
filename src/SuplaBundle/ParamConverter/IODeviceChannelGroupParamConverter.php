<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ChannelIconRepository;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;

class IODeviceChannelGroupParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ChannelIconRepository */
    private $channelIconRepository;

    public function __construct(
        LocationRepository $locationRepository,
        IODeviceChannelRepository $channelRepository,
        ChannelIconRepository $channelIconRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->channelRepository = $channelRepository;
        $this->channelIconRepository = $channelIconRepository;
    }

    public function getConvertedClass(): string {
        return IODeviceChannelGroup::class;
    }

    public function convert(array $data) {
        $user = $this->getCurrentUserOrThrow();
        $channelIds = $data['channelIds'] ?? [];
        Assertion::isArray($channelIds);
        Assertion::greaterThan(count($channelIds), 0, 'Channel group must consist of at least one channel.');
        $channels = array_map(function ($channelId) use ($user) {
            return $this->channelRepository->findForUser($user, $channelId);
        }, $channelIds);
        if (isset($data['locationId']) && $data['locationId']) {
            $location = $this->locationRepository->findForUser($user, $data['locationId']);
        } else {
            $location = $user->getLocations()[0];
        }
        $channelGroup = new IODeviceChannelGroup($user, $location, $channels);
        if (isset($data['userIconId']) && $data['userIconId']) {
            $user = $this->getCurrentUserOrThrow();
            $icon = $this->channelIconRepository->findForUser($user, $data['userIconId']);
            Assertion::eq($icon->getFunction()->getId(), $channelGroup->getFunction()->getId(), 'Chosen user icon is for other function.');
            $channelGroup->setUserIcon($icon);
        }
        $channelGroup->setCaption($data['caption'] ?? '');
        $channelGroup->setAltIcon($data['altIcon'] ?? 0);
        if (isset($data['hidden'])) {
            $channelGroup->setHidden(boolval($data['hidden']));
        }
        return $channelGroup;
    }
}
