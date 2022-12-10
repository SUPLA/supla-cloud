<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Location;

class LocationRepository extends EntityWithRelationsRepository {
    protected $alias = 'l';

    public function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('l entity')
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s c WHERE c.location = l) channels', IODeviceChannel::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s cg WHERE cg.location = l) channelGroups', IODeviceChannelGroup::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s io WHERE io.location = l) ioDevices', IODevice::class))
            ->addSelect('COUNT(DISTINCT aid) accessIds')
            ->from(Location::class, 'l')
            ->leftJoin('l.accessIds', 'aid')
            ->groupBy('l.id');
    }
}
