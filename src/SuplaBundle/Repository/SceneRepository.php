<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SceneRepository extends EntityRepository {
    public function findForUser(User $user, int $id): Scene {
        /** @var Scene $scene */
        $scene = $this->find($id);
        if (!$scene || !$scene->belongsToUser($user)) {
            throw new NotFoundHttpException("Scene ID$id could not be found.");
        }
        return $scene;
    }
}
