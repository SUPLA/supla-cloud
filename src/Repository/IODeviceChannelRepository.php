<?php
namespace App\Repository;

use App\Entity\Main\DirectLink;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\PushNotification;
use App\Entity\Main\SceneOperation;
use App\Entity\Main\Schedule;
use App\Entity\Main\ValueBasedTrigger;
use App\Enums\ChannelFunction;
use Doctrine\ORM\QueryBuilder;

class IODeviceChannelRepository extends EntityWithRelationsRepository {
    protected $alias = 'c';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('c entity')
            ->addSelect('COUNT(DISTINCT cg) channelGroups')
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s dl WHERE dl.channel = c) directLinks', DirectLink::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s s WHERE s.channel = c) schedules', Schedule::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s so WHERE so.channel = c) sceneOperations', SceneOperation::class))
            ->addSelect(sprintf('(SELECT COUNT(DISTINCT sos.owningScene) FROM %s sos WHERE sos.channel = c) scenes', SceneOperation::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s vbt WHERE vbt.owningChannel = c) ownReactions', ValueBasedTrigger::class))
            ->addSelect(sprintf(
                '(SELECT COUNT(1) FROM %s mpn WHERE mpn.channel = c AND mpn.managedByDevice = TRUE) managedNotifications',
                PushNotification::class
            ))
            ->addSelect(sprintf(
                '(SELECT COUNT(1) FROM %s at WHERE at.function = %d AND at.param1 = c.id) actionTriggers',
                IODeviceChannel::class,
                ChannelFunction::ACTION_TRIGGER
            ))
            ->from(IODeviceChannel::class, 'c')
            ->leftJoin('c.channelGroups', 'cg')
            ->groupBy('c.id');
    }

    /** @return IODeviceChannel[] */
    public function findActionTriggers(IODeviceChannel $channel): array {
        return $this->findBy(
            ['user' => $channel->getUser(), 'function' => ChannelFunction::ACTION_TRIGGER, 'param1' => $channel->getId()],
            ['channelNumber' => 'ASC', 'id' => 'ASC']
        );
    }
}
