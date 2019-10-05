<?php
namespace SuplaBundle\Entity\Common;

interface HasRelationsCount {
    /** @return array|null */
    public function getRelationsCount();
}
