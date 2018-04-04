<?php
namespace SuplaBundle\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Enums\AuditedAction;

class Audit {
    use CurrentUserAware;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function newEntry(AuditedAction $action): AuditEntryBuilder {
        return (new AuditEntryBuilder($this->entityManager))
            ->setUser($this->getCurrentUser())
            ->setAction($action);
    }
}
