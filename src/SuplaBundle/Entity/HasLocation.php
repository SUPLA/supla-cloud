<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Entity\Main\Location;

interface HasLocation {
    public function getLocation(): Location;
}
