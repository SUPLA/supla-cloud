<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\User;

/**
 * @method User|null findOneByEmail(string $email)
 */
class UserRepository extends EntityWithRelationsRepository {
    protected $alias = 'u';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('u entity')
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s cg WHERE cg.user = u) channelGroups', IODeviceChannelGroup::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s aid WHERE aid.user = u) accessIds', AccessID::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s l WHERE l.user = u) locations', Location::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s s WHERE s.user = u) schedules', Schedule::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s dl WHERE dl.user = u) directLinks', DirectLink::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s ac WHERE ac.user = u) apiClients', ApiClient::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s io WHERE io.user = u) ioDevices', IODevice::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s cl WHERE cl.user = u) clientApps', ClientApp::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s c WHERE c.user = u) channels', IODeviceChannel::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s sc WHERE sc.user = u) scenes', Scene::class))
            ->from(User::class, 'u');
    }
}
