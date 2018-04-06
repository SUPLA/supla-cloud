<?php
namespace SuplaBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\AuditEntry;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedAction;
use SuplaBundle\Enums\AuthenticationFailureReason;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\LockedException;

/**
 * @method User|null findOneByEmail(string $email)
 */
class UserRepository extends EntityRepository implements UserLoaderInterface {
    public function loadUserByUsername($username) {
        $user = $this->findOneByEmail($username);
        $limitExceeded = $this->isAuthenticationFailureLimitExceeded($username);
        if ($limitExceeded) {
            if (!$user) {
                throw new LockedException();
            }
            $user->lock();
        }
        return $user;
    }

    private function isAuthenticationFailureLimitExceeded(string $username): bool {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->in('action', [AuditedAction::AUTHENTICATION, AuditedAction::PASSWORD_RESET]))
            ->andWhere(Criteria::expr()->gte('createdAt', new \DateTime('-20minutes')))
            ->andWhere(Criteria::expr()->eq('textParam', $username))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->neq('intParam', AuthenticationFailureReason::BLOCKED),
                Criteria::expr()->isNull('intParam')
            ))
            ->orderBy(['createdAt' => 'DESC', 'id' => 'DESC'])
            ->setMaxResults(3);
        $authEntries = $this->getEntityManager()->getRepository(AuditEntry::class)->matching($criteria);
        if (count($authEntries) === 3) {
            foreach ($authEntries as $entry) {
                if ($entry->isSuccessful()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
