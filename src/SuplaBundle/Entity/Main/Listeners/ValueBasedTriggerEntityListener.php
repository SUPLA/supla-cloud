<?php

namespace SuplaBundle\Entity\Main\Listeners;

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Supla\SuplaServerAware;

class ValueBasedTriggerEntityListener {
    use SuplaServerAware;

    /**
     * @ORM\PostPersist
     * @ORM\PostRemove
     * @ORM\PostUpdate
     */
    public function notifySuplaServer() {
        $this->suplaServer->userAction('ON-VBT-CHANGED');
    }
}
