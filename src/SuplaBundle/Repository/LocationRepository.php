<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Location;

class LocationRepository extends AbstractRepository {
    protected $alias = 'l';

    public function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('l entity')
            ->addSelect('COUNT(DISTINCT c) channels')
            ->addSelect('COUNT(DISTINCT cg) channelGroups')
            ->addSelect('COUNT(DISTINCT io) iodevices')
            ->addSelect('COUNT(DISTINCT aid) accessIds')
            ->from(Location::class, 'l')
            ->leftJoin('l.channels', 'c')
            ->leftJoin('l.channelGroups', 'cg')
            ->leftJoin('l.ioDevices', 'io')
            ->leftJoin('l.accessIds', 'aid')
            ->groupBy('l');
    }
}
