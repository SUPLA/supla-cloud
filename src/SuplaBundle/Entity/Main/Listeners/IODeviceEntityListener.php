<?php

namespace SuplaBundle\Entity\Main\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Supla\SuplaServerAware;

class IODeviceEntityListener {
    use SuplaServerAware;

    /** @ORM\PostUpdate */
    public function postUpdate(IODevice $device, LifecycleEventArgs $args) {
        $changeArray = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($args->getObject());
        if (isset($changeArray['userConfig'])) {
            $this->suplaServer->deviceAction($device, 'USER-ON-DEVICE-CONFIG-CHANGED');
        }
        if (isset($changeArray['comment']) || isset($changeArray['enabled']) || isset($changeArray['location'])) {
            $this->suplaServer->reconnect();
        }
    }
}
