<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\UnexpectedResultException;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IODeviceRepository extends EntityRepository {
    /**
     * Finds IO Device by id that belongs to the given user.
     * @param User $user user that should own the device
     * @param int $id id of the device to return
     * @return IODevice found device
     * @throws NotFoundHttpException if the device does not exist or does not belong to the given user
     */
    public function findForUser(User $user, int $id): IODevice {
        /** @var IODevice $device */
        $device = $this->find($id);
        if (!$device || !$device->belongsToUser($user)) {
            throw new NotFoundHttpException("IO Device ID$id could not be found.");
        }
        return $device;
    }

    /**
     * Finds IO Device by id that belongs to the given user using GUID.
     * @param User $user user that should own the device
     * @param string $guid id of the device to return
     * @return IODevice found device
     * @throws NotFoundHttpException if the device does not exist or does not belong to the given user
     */
    public function findForUserByGuid(User $user, string $guid): IODevice {
        $guid = strtoupper(preg_replace('#[^\dA-F]#i', '', (preg_replace('#^0x#i', '', $guid))));
        $tableName = $this->getClassMetadata()->getTableName();
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(IODevice::class, 'io');
        $query = "SELECT {$rsm->generateSelectClause()} FROM $tableName io WHERE guid=UNHEX(:guid)";
        $device = $this->getEntityManager()->createNativeQuery($query, $rsm);
        $device->setParameter('guid', $guid);
        try {
            $device = $device->getSingleResult();
        } catch (UnexpectedResultException $e) {
            throw new NotFoundHttpException("IO Device GUID=$guid could not be found.", $e);
        }
        if (!$device || !$device->belongsToUser($user)) {
            throw new NotFoundHttpException("IO Device GUID=$guid could not be found.");
        }
        return $device;
    }
}
