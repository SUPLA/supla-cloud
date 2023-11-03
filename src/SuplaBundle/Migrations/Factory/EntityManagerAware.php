<?php

namespace SuplaBundle\Migrations\Factory;

use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerAware {
    public function setEntityManager(EntityManagerInterface $em): void;
}
