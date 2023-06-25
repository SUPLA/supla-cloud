<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Entity\Main\ValueBasedTrigger;

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
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s pn WHERE pn.user = u) pushNotifications', PushNotification::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s r WHERE r.user = u) valueBasedTriggers', ValueBasedTrigger::class))
            ->from(User::class, 'u');
    }
}
