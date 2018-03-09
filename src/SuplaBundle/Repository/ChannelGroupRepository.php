<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelGroupRepository extends EntityRepository {
    public function findForUser(User $user, int $id): IODeviceChannelGroup {
        /** @var IODeviceChannelGroup $channelGroup */
        $channelGroup = $this->find($id);
        if (!$channelGroup || !$channelGroup->belongsToUser($user)) {
            throw new NotFoundHttpException("ChannelGroup ID$id could not be found.");
        }
        return $channelGroup;
    }
}
