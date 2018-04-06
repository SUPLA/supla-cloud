<?php
namespace SuplaApiBundle\Model\Audit;

use Doctrine\ORM\EntityManagerInterface;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Enums\AuditedAction;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\AuditEntryRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class Audit {
    use CurrentUserAware;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;
    /** @var TimeProvider */
    private $timeProvider;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        AuditEntryRepository $auditEntryRepository,
        TimeProvider $timeProvider,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->auditEntryRepository = $auditEntryRepository;
        $this->timeProvider = $timeProvider;
        $this->requestStack = $requestStack;
    }

    public function newEntry(AuditedAction $action): AuditEntryBuilder {
        $request = $this->requestStack->getCurrentRequest();
        return (new AuditEntryBuilder($this->entityManager, $this->timeProvider))
            ->setUser($this->getCurrentUser())
            ->setIpv4($request ? $request->getClientIp() : null)
            ->setAction($action);
    }

    public function getRepository(): AuditEntryRepository {
        return $this->auditEntryRepository;
    }
}
