<?php
namespace SuplaBundle\Model\Audit;

use SuplaBundle\Enums\AuditedEvent;

trait AuditAware {
    /** @var Audit */
    protected $audit;

    /** @required */
    public function setAudit(Audit $audit) {
        $this->audit = $audit;
    }

    public function auditEntry(AuditedEvent $event): AuditEntryBuilder {
        return $this->audit->newEntry($event);
    }
}
