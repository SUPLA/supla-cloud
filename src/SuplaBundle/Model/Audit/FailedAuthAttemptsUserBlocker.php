<?php
namespace SuplaBundle\Model\Audit;

use Doctrine\Common\Collections\Criteria;
use SuplaBundle\Entity\Main\AuditEntry;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\AuthenticationFailureReason;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\AuditEntryRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class FailedAuthAttemptsUserBlocker {
    /** @var TimeProvider */
    private $timeProvider;
    /** @var RequestStack */
    private $requestStack;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;
    private $enabled;
    private $maxFailedAttempts;
    private $blockTimeInSeconds;

    public function __construct(
        TimeProvider $timeProvider,
        RequestStack $requestStack,
        AuditEntryRepository $auditEntryRepository,
        bool $enabled,
        int $maxFailedAttempts,
        int $blockTimeInSeconds
    ) {
        $this->timeProvider = $timeProvider;
        $this->requestStack = $requestStack;
        $this->auditEntryRepository = $auditEntryRepository;
        $this->enabled = $enabled;
        $this->maxFailedAttempts = $maxFailedAttempts;
        $this->blockTimeInSeconds = $blockTimeInSeconds;
    }

    public function isAuthenticationFailureLimitExceeded(string $username): bool {
        if (!$this->enabled) {
            return false;
        }
        $considerEntriesOlderThan = $this->timeProvider
            ->getDateTime(\DateInterval::createFromDateString("-$this->blockTimeInSeconds seconds"));
        $criteria = Criteria::create()
            ->where(Criteria::expr()->in('event', [
                AuditedEvent::AUTHENTICATION_SUCCESS,
                AuditedEvent::AUTHENTICATION_FAILURE,
                AuditedEvent::PASSWORD_RESET,
            ]))
            ->andWhere(Criteria::expr()->gte('createdAt', $considerEntriesOlderThan))
            ->andWhere(Criteria::expr()->eq('textParam', $username))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->neq('intParam', AuthenticationFailureReason::BLOCKED),
                Criteria::expr()->isNull('intParam')
            ))
            ->orderBy(['createdAt' => 'DESC', 'id' => 'DESC'])
            ->setMaxResults($this->maxFailedAttempts);
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $criteria->andWhere(Criteria::expr()->eq('ipv4', $request->getClientIp()));
        }
        $authEntries = $this->auditEntryRepository->matching($criteria)->toArray();
        if (count($authEntries) === $this->maxFailedAttempts) {
            foreach ($authEntries as $entry) {
                /** @var AuditEntry $entry */
                if ($entry->getEvent() != AuditedEvent::AUTHENTICATION_FAILURE()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
