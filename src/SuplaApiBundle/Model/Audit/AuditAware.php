<?php
namespace SuplaApiBundle\Model\Audit;

use SuplaBundle\Enums\AuditedAction;

trait AuditAware {
    /** @var Audit */
    protected $audit;

    /** @required */
    public function setAudit(Audit $audit) {
        $this->audit = $audit;
    }

    public function auditEntry(AuditedAction $action): AuditEntryBuilder {
        return $this->audit->newEntry($action);
    }
}
