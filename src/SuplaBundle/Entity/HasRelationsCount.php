<?php
namespace SuplaBundle\Entity;

interface HasRelationsCount {
    /** @return array|null */
    public function getRelationsCount();
}
