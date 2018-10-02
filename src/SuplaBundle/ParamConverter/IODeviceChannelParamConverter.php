<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ChannelIconRepository;
use SuplaBundle\Repository\LocationRepository;

class IODeviceChannelParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var ChannelIconRepository */
    private $channelIconRepository;

    public function getConvertedClass(): string {
        return IODeviceChannel::class;
    }

    public function __construct(LocationRepository $locationRepository, ChannelIconRepository $channelIconRepository) {
        $this->locationRepository = $locationRepository;
        $this->channelIconRepository = $channelIconRepository;
    }

    public function convert(array $requestData) {
        $channel = new IODeviceChannel();
        $function = $requestData['functionId'] ?? 0;
        $channel->setFunction($function);
        $channel->setParam1($requestData['param1'] ?? 0);
        $channel->setParam2($requestData['param2'] ?? 0);
        $channel->setParam3($requestData['param3'] ?? 0);
        $channel->setCaption($requestData['caption'] ?? '');
        $channel->setAltIcon($requestData['altIcon'] ?? 0);
        $channel->setHidden($requestData['hidden'] ?? false);
        if (isset($requestData['locationId']) && $requestData['locationId']
            && (!isset($requestData['inheritedLocation']) || !$requestData['inheritedLocation'])) {
            $user = $this->getCurrentUserOrThrow();
            $location = $this->locationRepository->findForUser($user, $requestData['locationId']);
            $channel->setLocation($location);
        }
        if (isset($requestData['userIconId']) && $requestData['userIconId']) {
            $user = $this->getCurrentUserOrThrow();
            $icon = $this->channelIconRepository->findForUser($user, $requestData['userIconId']);
            Assertion::eq($icon->getFunction()->getId(), $channel->getFunction()->getId(), 'Chosen user icon is for other function.');
            $channel->setUserIcon($icon);
        }
        return $channel;
    }
}
