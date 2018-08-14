<?php
namespace SuplaBundle\Entity;

/**
 * @method User getUser()
 */
trait BelongsToUser {
    /** @param User $me */
    public function belongsToUser($me): bool {
        if ($me instanceof User && ($owner = $this->getUser())) {
            return $me->getId() === $owner->getId();
        }
        return false;
    }
}
