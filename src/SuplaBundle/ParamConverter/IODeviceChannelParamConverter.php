<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Repository\UserIconRepository;

class IODeviceChannelParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var UserIconRepository */
    private $userIconRepository;
    /** @var ChannelParamConfigTranslator */
    private $paramsTranslator;

    public function getConvertedClass(): string {
        return IODeviceChannel::class;
    }

    public function __construct(
        LocationRepository $locationRepository,
        UserIconRepository $userIconRepository,
        ChannelParamConfigTranslator $paramsTranslator
    ) {
        $this->locationRepository = $locationRepository;
        $this->userIconRepository = $userIconRepository;
        $this->paramsTranslator = $paramsTranslator;
    }

    public function convert(array $requestData) {
        $channel = new IODeviceChannel();
        $function = $requestData['functionId'] ?? ChannelFunction::UNSUPPORTED;
        $channel->setFunction($function);
        $channel->setParam1($requestData['param1'] ?? 0);
        $channel->setParam2($requestData['param2'] ?? 0);
        $channel->setParam3($requestData['param3'] ?? 0);
        $channel->setTextParam1($requestData['textParam1'] ?? null);
        $channel->setTextParam2($requestData['textParam2'] ?? null);
        $channel->setTextParam3($requestData['textParam3'] ?? null);
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
            $icon = $this->userIconRepository->findForUser($user, $requestData['userIconId']);
            Assertion::eq($icon->getFunction()->getId(), $channel->getFunction()->getId(), 'Chosen user icon is for other function.');
            $channel->setUserIcon($icon);
        }
        return $channel;
    }
}
