<?php

namespace App\Entity\Main\Listeners;

use App\Entity\Main\Scene;
use App\Supla\SuplaServerAware;
use Doctrine\ORM\Mapping as ORM;

class SceneEntityListener {
    use SuplaServerAware;

    /** @ORM\PostPersist */
    public function postPersist(Scene $scene) {
        $this->suplaServer->postponeUserAction('ON-SCENE-ADDED', $scene->getId(), $scene->getUser());
    }

    /** @ORM\PostUpdate */
    public function postUpdate(Scene $scene) {
        $this->suplaServer->postponeUserAction('ON-SCENE-CHANGED', $scene->getId(), $scene->getUser());
    }

    /** @ORM\PreRemove */
    public function preRemove(Scene $scene) {
        $this->suplaServer->postponeUserAction('ON-SCENE-REMOVED', $scene->getId(), $scene->getUser());
    }
}
