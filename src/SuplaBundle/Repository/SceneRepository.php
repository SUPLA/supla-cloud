<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Main\Scene;

class SceneRepository extends EntityWithRelationsRepository {
    protected $alias = 's';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('s entity')
            ->addSelect('COUNT(DISTINCT so) operations')
            ->addSelect('COUNT(DISTINCT dl) directLinks')
            ->addSelect('COUNT(DISTINCT sch) schedules')
            ->addSelect('COUNT(DISTINCT sop) sceneOperations')
            ->addSelect('COUNT(DISTINCT sc) scenes')
            ->from(Scene::class, 's')
            ->leftJoin('s.operations', 'so')
            ->leftJoin('s.sceneOperations', 'sop')
            ->leftJoin('sop.owningScene', 'sc')
            ->leftJoin('s.directLinks', 'dl')
            ->leftJoin('s.schedules', 'sch')
            ->groupBy('s.id');
    }
}
