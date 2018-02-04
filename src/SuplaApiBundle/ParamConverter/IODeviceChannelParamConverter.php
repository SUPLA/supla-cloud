<?php
namespace SuplaApiBundle\ParamConverter;

use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Repository\LocationRepository;

class IODeviceChannelParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;

    public function getConvertedClass(): string {
        return IODeviceChannel::class;
    }

    public function __construct(LocationRepository $locationRepository) {
        $this->locationRepository = $locationRepository;
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
        if (isset($requestData['locationId']) && $requestData['locationId']) {
            $user = $this->getCurrentUserOrThrow();
            $location = $this->locationRepository->findForUser($user, $requestData['locationId']);
            $channel->setLocation($location);
        }
        return $channel;
    }
}
