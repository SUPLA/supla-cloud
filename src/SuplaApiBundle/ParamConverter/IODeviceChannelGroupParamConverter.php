<?php
namespace SuplaApiBundle\ParamConverter;

use Assert\Assertion;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;

class IODeviceChannelGroupParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(LocationRepository $locationRepository, IODeviceChannelRepository $channelRepository) {
        $this->locationRepository = $locationRepository;
        $this->channelRepository = $channelRepository;
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
        $channelGroup->setCaption($data['caption'] ?? '');
        if (isset($data['hidden'])) {
            $channelGroup->setHidden(boolval($data['hidden']));
        }
        if (isset($data['enabled'])) {
            $channelGroup->setEnabled(boolval($data['enabled']));
        }
        return $channelGroup;
    }
}
