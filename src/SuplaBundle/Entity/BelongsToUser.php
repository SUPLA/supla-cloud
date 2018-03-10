<?php
namespace SuplaBundle\Entity;

use SuplaApiBundle\Entity\OAuth\ApiUser;

/**
 * @method User getUser()
 */
trait BelongsToUser {
    /** @param User|ApiUser $me */
    public function belongsToUser($me): bool {
        if ($me) {
            if ($me instanceof User) {
                return $me->getId() == $this->getUser()->getId();
            } elseif ($me instanceof \SuplaApiBundle\Entity\OAuth\ApiUser) {
                return $me->getParentUser()->getId() == $this->getUser()->getId();
            }
        }
        return false;
    }
}
