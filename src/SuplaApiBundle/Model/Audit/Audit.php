<?php
namespace SuplaApiBundle\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Enums\AuditedAction;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        AuditEntryRepository $auditEntryRepository,
        TimeProvider $timeProvider
    ) {
        $this->entityManager = $entityManager;
        $this->auditEntryRepository = $auditEntryRepository;
        $this->timeProvider = $timeProvider;
    }

    public function newEntry(AuditedAction $action): AuditEntryBuilder {
        return (new AuditEntryBuilder($this->entityManager, $this->timeProvider))
            ->setUser($this->getCurrentUser())
            ->setAction($action);
    }

    public function getRepository(): AuditEntryRepository {
        return $this->auditEntryRepository;
    }
}
