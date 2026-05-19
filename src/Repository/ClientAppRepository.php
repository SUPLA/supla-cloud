<?php
namespace App\Repository;

use App\Entity\Main\ClientApp;
use App\Entity\Main\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientAppRepository extends EntityRepository {
    public function findForUser(User $user, int $id): ClientApp {
        /** @var ClientApp $clientApp */
        $clientApp = $this->find($id);
        if (!$clientApp || !$clientApp->belongsToUser($user)) {
            throw new NotFoundHttpException("ClientApp ID$id could not be found.");
        }
        return $clientApp;
    }
}
