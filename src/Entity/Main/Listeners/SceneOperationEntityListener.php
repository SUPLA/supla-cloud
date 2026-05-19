<?php

namespace App\Entity\Main\Listeners;

use App\Entity\Main\SceneOperation;
use App\Supla\SuplaServerAware;
use Doctrine\ORM\Mapping as ORM;

class SceneOperationEntityListener {
    use SuplaServerAware;

    /** @ORM\PostRemove */
    public function postRemove(SceneOperation $sceneOperation) {
        $scene = $sceneOperation->getOwningScene();
        $this->suplaServer->postponeUserAction('ON-SCENE-CHANGED', $scene->getId(), $scene->getUser());
    }
}
