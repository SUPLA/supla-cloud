<?php
namespace SuplaBundle\Model\Audit;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\AuditEntry;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedAction;

class AuditEntryBuilder {
    /** @var AuditedAction */
    private $action;
    /** @var User|null */
    private $user;
    private $textParam1, $textParam2;
    private $successful = true;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
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

    public function setSuccessful(bool $successful): AuditEntryBuilder {
        $this->successful = $successful;
        return $this;
    }

    public function unsuccessful(): AuditEntryBuilder {
        return $this->setSuccessful(false);
    }

    public function setTextParam(string $value, int $paramNo = 1): AuditEntryBuilder {
        $paramName = 'textParam' . $paramNo;
        $this->{$paramName} = $value;
        return $this;
    }

    public function setTextParam2($value) {
        return $this->setTextParam($value, 2);
    }

    public function build(): AuditEntry {
        Assertion::notNull($this->action, 'Audit Entry must have an action.');
        return new AuditEntry($this->action, $this->user, $this->successful, $this->textParam1, $this->textParam2);
    }

    public function buildAndSave() {
        $this->entityManager->persist($this->build());
    }

    public function buildAndFlush() {
        $this->buildAndSave();
        $this->entityManager->flush();
    }
}
