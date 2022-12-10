<?php
namespace SuplaBundle\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\AuditEntry;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\RealClientIpResolver;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\AuditEntryRepository;

class Audit {
    use CurrentUserAware;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var RealClientIpResolver */
    private $clientIpResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        AuditEntryRepository $auditEntryRepository,
        TimeProvider $timeProvider,
        RealClientIpResolver $clientIpResolver
    ) {
        $this->entityManager = $entityManager;
        $this->auditEntryRepository = $auditEntryRepository;
        $this->timeProvider = $timeProvider;
        $this->clientIpResolver = $clientIpResolver;
    }

    public function newEntry(AuditedEvent $event): AuditEntryBuilder {
        return (new AuditEntryBuilder($this->entityManager, $this->timeProvider))
            ->setUser($this->getCurrentUser())
            ->setIpv4($this->clientIpResolver->getRealIp())
            ->setEvent($event);
    }

    /** @return AuditEntry|null */
    public function recentEntry(AuditedEvent $event, $period = 'PT5M', User $user = null) {
        $date = $this->timeProvider->getDateTime();
        $date->setTimeZone(new \DateTimeZone('UTC'));
        $date->sub(new \DateInterval($period));
        $user = $user ?: $this->getCurrentUserOrThrow();
        $qb = $this->getRepository()->createQueryBuilder('ae');
        $recentEntry = $qb
                ->where('ae.event IN(:events)')
                ->andWhere('ae.user = :user')
                ->andWhere($qb->expr()->gte('ae.createdAt', ':date'))
                ->setParameters([
                    'user' => $user,
                    'events' => [$event->getId()],
                    'date' => $date,
                ])
                ->getQuery()
                ->getResult()[0] ?? null;
        return $recentEntry;
    }

    public function getRepository(): AuditEntryRepository {
        return $this->auditEntryRepository;
    }
}
