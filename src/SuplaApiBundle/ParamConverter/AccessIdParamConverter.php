<?php
namespace SuplaApiBundle\ParamConverter;

use Assert\Assertion;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Repository\LocationRepository;

class AccessIdParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;

    public function getConvertedClass(): string {
        return AccessID::class;
    }

    public function __construct(LocationRepository $locationRepository) {
        $this->locationRepository = $locationRepository;
    }

    public function convert(array $requestData) {
        $accessId = new AccessID();
        $user = $this->getCurrentUserOrThrow();
        $accessId->setEnabled(boolval($requestData['enabled'] ?? false));
        $accessId->setCaption($requestData['caption'] ?? '');
        $accessId->setPassword($requestData['password'] ?? '');
        if (isset($requestData['locationsIds'])) {
            Assertion::isArray($requestData['locationsIds']);
            foreach ($requestData['locationsIds'] as $id) {
                $location = $this->locationRepository->findForUser($user, $id);
                $accessId->getLocations()->add($location);
            }
        }
        return $accessId;
    }
}
