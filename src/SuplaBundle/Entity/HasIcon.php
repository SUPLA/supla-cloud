<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Entity\Main\UserIcon;

interface HasIcon {
    public function getAltIcon(): int;

    public function getUserIcon(): ?UserIcon;
}
