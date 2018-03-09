<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccessIdRepository extends EntityRepository {
    public function findForUser(User $user, int $id): AccessID {
        /** @var AccessID $accessId */
        $accessId = $this->find($id);
        if (!$accessId || !$accessId->belongsToUser($user)) {
            throw new NotFoundHttpException("AccessID ID$id could not be found.");
        }
        return $accessId;
    }
}
