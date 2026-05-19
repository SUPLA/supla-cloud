<?php

namespace App\Entity\Main\Listeners;

use App\Entity\Main\ValueBasedTrigger;
use App\Supla\SuplaServerAware;
use Doctrine\ORM\Mapping as ORM;

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
