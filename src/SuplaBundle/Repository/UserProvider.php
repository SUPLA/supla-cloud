<?php
namespace SuplaBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use SuplaApiBundle\Model\Audit\FailedAuthAttemptsUserBlocker;
use SuplaBundle\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\LockedException;

class UserProvider extends EntityUserProvider {
    /** @var FailedAuthAttemptsUserBlocker */
    private $failedAuthAttemptsUserBlocker;

    public function __construct(ManagerRegistry $registry, FailedAuthAttemptsUserBlocker $failedAuthAttemptsUserBlocker) {
        parent::__construct($registry, User::class, 'email');
        $this->failedAuthAttemptsUserBlocker = $failedAuthAttemptsUserBlocker;
    }

    public function loadUserByUsername($username) {
        if ($this->failedAuthAttemptsUserBlocker->isAuthenticationFailureLimitExceeded($username)) {
            throw new LockedException();
        }
        return parent::loadUserByUsername($username);
    }
}
