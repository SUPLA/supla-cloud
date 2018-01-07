<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocationRepository extends EntityRepository {
    public function findForUser(User $user, int $id): Location {
        /** @var Location $location */
        $location = $this->find($id);
        if (!$location || $location->getUser()->getId() != $user->getId()) {
            throw new NotFoundHttpException("Location ID$id could not be found.");
        }
        return $location;
    }
}
