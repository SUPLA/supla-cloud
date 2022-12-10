<?php
namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\AccessIdRepository;

class LocationParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var AccessIdRepository */
    private $accessIdRepository;

    public function getConvertedClass(): string {
        return Location::class;
    }

    public function __construct(AccessIdRepository $accessIdRepository) {
        $this->accessIdRepository = $accessIdRepository;
    }

    public function convert(array $requestData) {
        $location = new Location();
        $user = $this->getCurrentUserOrThrow();
        $location->setEnabled(boolval($requestData['enabled'] ?? false));
        $location->setCaption($requestData['caption'] ?? '');
        Assertion::maxLength($location->getCaption(), 100, 'Caption is too long.'); // i18n
        $location->setPassword($requestData['password'] ?? '');
        if (isset($requestData['accessIdsIds'])) {
            Assertion::isArray($requestData['accessIdsIds']);
            foreach ($requestData['accessIdsIds'] as $id) {
                $accessId = $this->accessIdRepository->findForUser($user, $id);
                $location->getAccessIds()->add($accessId);
            }
        }
        return $location;
    }
}
