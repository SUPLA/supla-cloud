<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IODeviceChannelRepository extends EntityRepository {
    public function findForUser(User $user, int $id): IODeviceChannel {
        /** @var IODeviceChannel $channel */
        $channel = $this->find($id);
        if (!$channel || $channel->getIoDevice()->getUser()->getId() != $user->getId()) {
            throw new NotFoundHttpException("Channel ID$id could not be found.");
        }
        return $channel;
    }
}
