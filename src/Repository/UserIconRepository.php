<?php
namespace App\Repository;

use App\Entity\Main\User;
use App\Entity\Main\UserIcon;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserIconRepository extends EntityRepository {
    public function findForUser(User $user, int $id): UserIcon {
        /** @var UserIcon $userIcon */
        $userIcon = $this->find($id);
        if (!$userIcon || !$userIcon->belongsToUser($user)) {
            throw new NotFoundHttpException("UserIcon ID$id could not be found.");
        }
        return $userIcon;
    }
}
