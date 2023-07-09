<?php

namespace SuplaBundle\Entity\Main\Listeners;

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Supla\SuplaServerAware;

class ValueBasedTriggerEntityListener {
    use SuplaServerAware;

    /**
     * @ORM\PostPersist
     * @ORM\PostRemove
     * @ORM\PostUpdate
     */
    public function notifySuplaServer(ValueBasedTrigger $vbt) {
        $this->suplaServer->postponeUserAction('ON-VBT-CHANGED', [], $vbt->getUser());
    }
}
