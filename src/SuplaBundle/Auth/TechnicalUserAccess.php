<?php

namespace SuplaBundle\Auth;

use SuplaBundle\Entity\Main\User;

readonly class TechnicalUserAccess {
    public function __construct(private User $user) {
    }

    public function getUser(): User {
        return $this->user;
    }
}
