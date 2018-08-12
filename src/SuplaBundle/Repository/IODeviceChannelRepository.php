<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IODeviceChannelRepository extends EntityRepository {
    /**
     * Finds channel by id that belongs to the given user.
     * @param User $user user that should own the chanenl
     * @param int $id id of the channel to return
     * @return IODeviceChannel found channel
     * @throws NotFoundHttpException if the channel does not exist or does not belong to the given user
     */
    public function findForUser(User $user, int $id): IODeviceChannel {
        /** @var IODeviceChannel $channel */
        $channel = $this->find($id);
        if (!$channel || !$channel->belongsToUser($user)) {
            throw new NotFoundHttpException("Channel ID$id could not be found.");
        }
        return $channel;
    }
}
