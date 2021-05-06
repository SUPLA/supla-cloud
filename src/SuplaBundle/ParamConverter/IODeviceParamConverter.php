<?php
namespace SuplaBundle\ParamConverter;

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\LocationRepository;

class IODeviceParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;

    public function getConvertedClass(): string {
        return IODevice::class;
    }

    public function __construct(LocationRepository $locationRepository) {
        $this->locationRepository = $locationRepository;
    }

    public function convert(array $requestData) {
        $device = new IODevice();
        $device->setEnabled($requestData['enabled'] ?? false);
        $device->setComment($requestData['comment'] ?? '');
        if (isset($requestData['locationId']) && $requestData['locationId']) {
            $user = $this->getCurrentUserOrThrow();
            $location = $this->locationRepository->findForUser($user, $requestData['locationId']);
            $device->setLocation($location);
        }
        return $device;
    }
}
