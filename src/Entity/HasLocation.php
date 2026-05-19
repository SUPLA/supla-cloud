<?php
namespace App\Entity;

use App\Entity\Main\Location;

interface HasLocation {
    public function getLocation(): Location;
}
