<?php
namespace App\Entity;

use App\Entity\Main\UserIcon;

interface HasIcon {
    public function getAltIcon(): int;

    public function getUserIcon(): ?UserIcon;
}
