<?php

namespace SuplaBundle\Entity\Main\Listeners;

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Supla\SuplaServerAware;

class SceneOperationEntityListener {
    use SuplaServerAware;

    /** @ORM\PostRemove */
    public function postRemove(SceneOperation $sceneOperation) {
        $scene = $sceneOperation->getOwningScene();
        $this->suplaServer->postponeUserAction('ON-SCENE-CHANGED', $scene->getId(), $scene->getUser());
    }
}
