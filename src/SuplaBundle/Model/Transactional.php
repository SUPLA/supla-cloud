<?php
namespace SuplaBundle\Model;

use Doctrine\ORM\EntityManagerInterface;

trait Transactional {
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function transactional(callable $function) {
        return $this->entityManager->transactional($function);
    }
}
