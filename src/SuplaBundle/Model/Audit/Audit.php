<?php
namespace SuplaBundle\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
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

    public function getRepository(): AuditEntryRepository {
        return $this->auditEntryRepository;
    }
}
