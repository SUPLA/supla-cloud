<?php
namespace SuplaApiBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ClientAppRepository;
use SuplaBundle\Repository\LocationRepository;

class AccessIdParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var LocationRepository */
    private $locationRepository;
    /** @var ClientAppRepository */
    private $clientAppRepository;

    public function getConvertedClass(): string {
        return AccessID::class;
    }

    public function __construct(LocationRepository $locationRepository, ClientAppRepository $clientAppRepository) {
        $this->locationRepository = $locationRepository;
        $this->clientAppRepository = $clientAppRepository;
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
        if (isset($requestData['clientAppsIds'])) {
            Assertion::isArray($requestData['clientAppsIds']);
            foreach ($requestData['clientAppsIds'] as $id) {
                $clientApp = $this->clientAppRepository->findForUser($user, $id);
                $accessId->getClientApps()->add($clientApp);
            }
        }
        return $accessId;
    }
}
