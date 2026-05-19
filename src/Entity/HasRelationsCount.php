<?php
namespace App\Entity;

interface HasRelationsCount {
    /** @return array|null */
    public function getRelationsCount();
}
