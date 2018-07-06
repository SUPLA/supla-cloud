<?php
namespace SuplaBundle\Entity;

/**
 * @method User getUser()
 */
trait BelongsToUser {
    /** @param User $me */
    public function belongsToUser($me): bool {
        if ($me instanceof User) {
            return $me->getId() == $this->getUser()->getId();
        }
        return false;
    }
}
