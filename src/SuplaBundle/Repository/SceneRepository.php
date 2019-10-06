<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Scene;

class SceneRepository extends EntityWithRelationsRepository {
    protected $alias = 's';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('s entity')
            ->addSelect('COUNT(DISTINCT so) operations')
            ->from(Scene::class, 's')
            ->leftJoin('s.operations', 'so')
            ->groupBy('s.id');
    }
}
