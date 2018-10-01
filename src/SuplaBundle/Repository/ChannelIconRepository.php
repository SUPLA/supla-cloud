<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\ChannelIcon;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelIconRepository extends EntityRepository {
    public function findForUser(User $user, int $id): ChannelIcon {
        /** @var ChannelIcon $channelIcon */
        $channelIcon = $this->find($id);
        if (!$channelIcon || !$channelIcon->belongsToUser($user)) {
            throw new NotFoundHttpException("ChannelIcon ID$id could not be found.");
        }
        return $channelIcon;
    }
}
