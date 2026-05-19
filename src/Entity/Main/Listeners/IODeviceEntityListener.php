<?php

namespace App\Entity\Main\Listeners;

use App\Entity\Main\IODevice;
use App\Supla\SuplaServerAware;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

class IODeviceEntityListener {
    use SuplaServerAware;

    /** @ORM\PostUpdate */
    public function postUpdate(IODevice $device, LifecycleEventArgs $args) {
        $changeArray = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($args->getObject());
        if (isset($changeArray['userConfig'])) {
            $this->suplaServer->postponeCommand('USER-ON-DEVICE-CONFIG-CHANGED', [$device->getUser()->getId(), $device->getId()]);
        }
        if (isset($changeArray['comment']) || isset($changeArray['enabled']) || isset($changeArray['location'])) {
            $this->suplaServer->reconnect();
        }
    }
}
