<?php
namespace SuplaBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocationRepository extends EntityRepository {
    public function find($id, $lockMode = null, $lockVersion = null) {
        $query = $this->getEntityWithRelationsCountQuery()
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        $result = $query->getSingleResult();
        return $this->hydrateRelationsQueryResult($result);
    }

    public function findForUser(User $user, int $id): Location {
        /** @var Location $location */
        $location = $this->find($id);
        if (!$location || $location->getUser()->getId() != $user->getId()) {
            throw new NotFoundHttpException("Location ID$id could not be found.");
        }
        return $location;
    }

    /** @return Collection|Location[] */
    public function findAllForUser(User $user): Collection {
        $query = $this->getEntityWithRelationsCountQuery()
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        $results = $query->getResult();
        $channelGroups = array_map([$this, 'hydrateRelationsQueryResult'], $results);
        return new ArrayCollection($channelGroups);
    }

    public function hydrateRelationsQueryResult(array $result): Location {
        /** @var Location $location */
        $location = $result['location'];
        unset($result['location']);
        $location->setRelationsCount($result);
        return $location;
    }

    private function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('l location')
            ->addSelect('COUNT(c) channels')
            ->addSelect('COUNT(cg) channelGroups')
            ->addSelect('COUNT(io) iodevices')
            ->addSelect('COUNT(aid) accessIds')
            ->from(Location::class, 'l')
            ->leftJoin('l.channels', 'c')
            ->leftJoin('l.channelGroups', 'cg')
            ->leftJoin('l.ioDevices', 'io')
            ->leftJoin('l.accessIds', 'aid')
            ->groupBy('l');
    }
}
