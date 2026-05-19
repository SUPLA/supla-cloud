<?php
namespace App\Repository;

use App\Entity\Main\AccessID;
use App\Entity\Main\ClientApp;
use App\Entity\Main\DirectLink;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\IODeviceChannelGroup;
use App\Entity\Main\Location;
use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\PushNotification;
use App\Entity\Main\Scene;
use App\Entity\Main\Schedule;
use App\Entity\Main\User;
use App\Entity\Main\ValueBasedTrigger;
use Doctrine\ORM\QueryBuilder;

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
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s c WHERE c.user = u AND c.isVirtual = false) channels', IODeviceChannel::class))
            ->addSelect(sprintf(
                '(SELECT COUNT(1) FROM %s cv WHERE cv.user = u AND cv.isVirtual = true) virtualChannels',
                IODeviceChannel::class,
            ))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s sc WHERE sc.user = u) scenes', Scene::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s pn WHERE pn.user = u) pushNotifications', PushNotification::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s r WHERE r.user = u) valueBasedTriggers', ValueBasedTrigger::class))
            ->from(User::class, 'u');
    }
}
