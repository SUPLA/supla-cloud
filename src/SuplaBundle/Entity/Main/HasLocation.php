<?php
namespace SuplaBundle\Entity\Main;

interface HasLocation {
    public function getLocation(): Location;
}
