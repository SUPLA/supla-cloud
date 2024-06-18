<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Entity\Main\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IODeviceRepository extends EntityWithRelationsRepository {
    protected $alias = 'io';

    /**
     * Finds IO Device by id that belongs to the given user using GUID.
     * @param \SuplaBundle\Entity\Main\User $user user that should own the device
     * @param string $guid id of the device to return
     * @return \SuplaBundle\Entity\Main\IODevice found device
     * @throws NotFoundHttpException if the device does not exist or does not belong to the given user
     */
    public function findForUserByGuid(User $user, string $guid): IODevice {
        $guid = strtoupper(preg_replace('#[^\dA-F]#i', '', (preg_replace('#^0x#i', '', $guid))));
        $unhexed = pack('H*', $guid);
        $query = $this->getEntityWithRelationsCountQuery()
            ->where('io.guid = :guid')
            ->andWhere('io.user = :user')
            ->setParameter('guid', $unhexed)
            ->setParameter('user', $user)
            ->getQuery();
        try {
            $device = $this->hydrateRelationsQueryResult($query->getSingleResult());
        } catch (UnexpectedResultException $e) {
            throw new NotFoundHttpException("IO Device GUID=$guid could not be found.", $e);
        }
        if (!$device || !$device->belongsToUser($user)) {
            throw new NotFoundHttpException("IO Device GUID=$guid could not be found.");
        }
        return $device;
    }

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('io entity')
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s c WHERE c.iodevice = io) channels', IODeviceChannel::class))
            ->addSelect(sprintf(
                '(SELECT COUNT(1) FROM %s c2 WHERE c2.iodevice = io AND c2.conflictDetails IS NOT NULL) channelsWithConflict',
                IODeviceChannel::class
            ))
            ->addSelect(sprintf(
                '(SELECT COUNT(1) FROM %s mpn WHERE mpn.device = io AND mpn.channel IS NULL AND mpn.managedByDevice = TRUE) %s',
                PushNotification::class,
                'managedNotifications'
            ))
            ->from(IODevice::class, 'io');
    }
}
