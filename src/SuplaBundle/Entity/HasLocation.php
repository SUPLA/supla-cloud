<?php
namespace SuplaBundle\Entity;

interface HasLocation {
    public function getLocation(): Location;
}
