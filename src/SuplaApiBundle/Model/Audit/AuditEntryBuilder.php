<?php
namespace SuplaApiBundle\Model\Audit;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use MyCLabs\Enum\Enum;
use SuplaBundle\Entity\AuditEntry;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedAction;
use SuplaBundle\Model\TimeProvider;

class AuditEntryBuilder {
    /** @var AuditedAction */
    private $action;
    /** @var User|null */
    private $user;
    private $textParam;
    private $intParam;
    private $successful = true;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var TimeProvider */
    private $timeProvider;
    private $ipv4;

    public function __construct(EntityManagerInterface $entityManager, TimeProvider $timeProvider = null) {
        $this->entityManager = $entityManager;
        $this->timeProvider = $timeProvider ?: new TimeProvider();
    }

    public function setAction(AuditedAction $action): AuditEntryBuilder {
        $this->action = $action;
        return $this;
    }

    /** @param User|null $user */
    public function setUser($user): AuditEntryBuilder {
        $this->user = $user;
        return $this;
    }

    public function setIpv4($ipv4): AuditEntryBuilder {
        $this->ipv4 = $ipv4 ? ip2long($ipv4) : null;
        return $this;
    }

    public function setSuccessful(bool $successful): AuditEntryBuilder {
        $this->successful = $successful;
        return $this;
    }

    public function unsuccessful(): AuditEntryBuilder {
        return $this->setSuccessful(false);
    }

    public function setTextParam(string $value): AuditEntryBuilder {
        $this->textParam = $value;
        return $this;
    }

    public function setIntParam($value): AuditEntryBuilder {
        if ($value instanceof Enum) {
            $value = $value->getValue();
        }
        Assertion::numeric($value);
        $this->intParam = $value;
        return $this;
    }

    public function build(): AuditEntry {
        Assertion::notNull($this->action, 'Audit Entry must have an action.');
        return new AuditEntry(
            $this->timeProvider->getDateTime(),
            $this->action,
            $this->user,
            $this->ipv4,
            $this->successful,
            $this->textParam,
            $this->intParam
        );
    }

    public function buildAndSave() {
        $this->entityManager->persist($this->build());
    }

    public function buildAndFlush() {
        $this->buildAndSave();
        $this->entityManager->flush();
    }
}
