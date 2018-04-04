<?php
namespace SuplaApiBundle\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Enums\AuditedAction;
use SuplaBundle\Repository\AuditEntryRepository;

class Audit {
    use CurrentUserAware;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    public function __construct(EntityManagerInterface $entityManager, AuditEntryRepository $auditEntryRepository) {
        $this->entityManager = $entityManager;
        $this->auditEntryRepository = $auditEntryRepository;
    }

    public function newEntry(AuditedAction $action): AuditEntryBuilder {
        return (new AuditEntryBuilder($this->entityManager))
            ->setUser($this->getCurrentUser())
            ->setAction($action);
    }

    public function getRepository(): AuditEntryRepository {
        return $this->auditEntryRepository;
    }
}
