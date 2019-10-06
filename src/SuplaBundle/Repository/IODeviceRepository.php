<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnexpectedResultException;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IODeviceRepository extends AbstractRepository {
    protected $alias = 'io';

    /**
     * Finds IO Device by id that belongs to the given user using GUID.
     * @param User $user user that should own the device
     * @param string $guid id of the device to return
     * @return IODevice found device
     * @throws NotFoundHttpException if the device does not exist or does not belong to the given user
     */
    public function findForUserByGuid(User $user, string $guid): IODevice {
        $guid = strtoupper(preg_replace('#[^\dA-F]#i', '', (preg_replace('#^0x#i', '', $guid))));
        $unhexed = pack('H*', $guid);
        $query = $this->getEntityWithRelationsCountQuery()
            ->where('io.guid = :guid')
            ->setParameter('guid', $unhexed)
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
            ->addSelect('COUNT(DISTINCT c) channels')
            ->from(IODevice::class, 'io')
            ->leftJoin('io.channels', 'c')
            ->groupBy('io');
    }
}
