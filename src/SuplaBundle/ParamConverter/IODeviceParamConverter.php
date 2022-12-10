<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\Location;
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
        Assertion::maxLength($device->getComment(), 200, 'Caption is too long.'); // i18n
        if (isset($requestData['locationId']) && $requestData['locationId']) {
            $user = $this->getCurrentUserOrThrow();
            $location = $this->locationRepository->findForUser($user, $requestData['locationId']);
            $device->setLocation($location);
        } else {
            $device->setLocation(new Location());
        }
        return $device;
    }
}
