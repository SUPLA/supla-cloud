<?php
namespace SuplaBundle\Serialization\RequestFiller;

use Assert\Assertion;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Repository\UserIconRepository;

class ChannelGroupRequestFiller extends AbstractRequestFiller {
    use CurrentUserAware;
    use UserAltIconRequestFiller;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var UserIconRepository */
    private $userIconRepository;

    public function __construct(
        LocationRepository $locationRepository,
        IODeviceChannelRepository $channelRepository,
        UserIconRepository $userIconRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->channelRepository = $channelRepository;
        $this->userIconRepository = $userIconRepository;
    }

    /** @param IODeviceChannelGroup $channelGroup */
    public function fillFromData(array $data, $channelGroup = null) {
        $user = $this->getCurrentUserOrThrow();
        if (!$channelGroup) {
            $data = array_merge([
                'channelsIds' => [],
            ], $data);
            $channelGroup = new IODeviceChannelGroup($user, $user->getLocations()[0]);
        }
        if (array_key_exists('locationId', $data)) {
            Assertion::greaterThan($data['locationId'], 0);
            $location = $this->locationRepository->findForUser($user, $data['locationId']);
            $channelGroup->setLocation($location);
        }
        if (array_key_exists('channelsIds', $data)) {
            $channelIds = $data['channelsIds'];
            Assertion::isArray($channelIds);
            Assertion::greaterThan(count($channelIds), 0, 'Channel group must consist of at least one channel.'); // i18n
            $channels = array_map(function ($channelId) use ($user) {
                return $this->channelRepository->findForUser($user, $channelId);
            }, $channelIds);
            $channelGroup->setChannels($channels);
            Assertion::lessOrEqualThan(
                $channelGroup->getChannels()->count(),
                $user->getLimitChannelPerGroup(),
                'Too many channels in this group' // i18n
            );
        }
        if (isset($data['caption'])) {
            Assertion::string($data['caption'], null, 'caption');
            $channelGroup->setCaption($data['caption'] ?? '');
            Assertion::maxLength($channelGroup->getCaption(), 100, 'Caption is too long.'); // i18n
        }
        if (array_key_exists('hidden', $data)) {
            $channelGroup->setHidden(boolval($data['hidden']));
        }
        $this->fillUserAltIcon($this->userIconRepository, $data, $channelGroup);
        return $channelGroup;
    }
}
