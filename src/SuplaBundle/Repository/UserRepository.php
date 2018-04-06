<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaApiBundle\Model\Audit\FailedAuthAttemptsUserBlocker;
use SuplaBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @method User|null findOneByEmail(string $email)
 */
class UserRepository extends EntityRepository implements UserProviderInterface {
    /** @var FailedAuthAttemptsUserBlocker */
    private $failedAuthAttemptsUserBlocker;

    /** @required */
    public function setFailedAuthAttemptsUserBlocker(FailedAuthAttemptsUserBlocker $failedAuthAttemptsUserBlocker) {
        $this->failedAuthAttemptsUserBlocker = $failedAuthAttemptsUserBlocker;
    }

    public function loadUserByUsername($username) {
        $user = $this->findOneByEmail($username);
        if ($this->failedAuthAttemptsUserBlocker->isAuthenticationFailureLimitExceeded($username)) {
            throw new LockedException();
        }
        if (!$user) {
            throw new UsernameNotFoundException();
        }
        return $user;
    }

    public function refreshUser(UserInterface $user) {
        $this->getEntityManager()->refresh($user);
    }

    public function supportsClass($class) {
        return $class === User::class;
    }
}
