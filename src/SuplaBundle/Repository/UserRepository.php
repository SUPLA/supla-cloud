<?php
namespace SuplaBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use SuplaBundle\Entity\AuditEntry;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedAction;
use SuplaBundle\Enums\AuthenticationFailureReason;
use SuplaBundle\Model\TimeProvider;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\LockedException;

/**
 * @method User|null findOneByEmail(string $email)
 */
class UserRepository extends EntityRepository implements UserLoaderInterface {
    /** @var TimeProvider */
    private $timeProvider;

    public function __construct($em, ClassMetadata $class) {
        parent::__construct($em, $class);
        $this->timeProvider = new TimeProvider();
    }

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
        $considerEntriesOlderThan = $this->timeProvider->getDateTime(\DateInterval::createFromDateString('-20 minutes'));
        $criteria = Criteria::create()
            ->where(Criteria::expr()->in('action', [AuditedAction::AUTHENTICATION, AuditedAction::PASSWORD_RESET]))
            ->andWhere(Criteria::expr()->gte('createdAt', $considerEntriesOlderThan))
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
