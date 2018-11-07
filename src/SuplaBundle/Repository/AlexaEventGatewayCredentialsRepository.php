<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\User;
use SuplaBundle\Entity\AlexaEventGatewayCredentials;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlexaEventGatewayCredentialsRepository extends EntityRepository {
    public function findForUser(User $user, int $id): AlexaEventGatewayCredentials {
        /** @var AlexaEventGatewayCredentials $alexaEventGateway */
        $alexaEventGateway = $this->find($id);
        if (!$alexaEventGateway || !$alexaEventGateway->belongsToUser($user)) {
            throw new NotFoundHttpException("AlexaEventGateway ID$id could not be found.");
        }
        return $alexaEventGateway;
    }
}
