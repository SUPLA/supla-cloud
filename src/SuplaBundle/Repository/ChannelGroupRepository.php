<?php
namespace SuplaBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelGroupRepository extends EntityRepository {
    public function find($id, $lockMode = null, $lockVersion = null) {
        $query = $this->getEntityWithRelationsCountQuery()
            ->addSelect('l')
            ->innerJoin('cg.location', 'l')
            ->where('cg.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        $result = $query->getSingleResult();
        return $this->hydrateRelationsQueryResult($result);
    }

    public function findForUser(User $user, int $id): IODeviceChannelGroup {
        /** @var IODeviceChannelGroup $channelGroup */
        $channelGroup = $this->find($id);
        if (!$channelGroup || !$channelGroup->belongsToUser($user)) {
            throw new NotFoundHttpException("ChannelGroup ID$id could not be found.");
        }
        return $channelGroup;
    }

    /** @return Collection|IODeviceChannelGroup[] */
    public function findAllForUser(User $user): Collection {
        $query = $this->getEntityWithRelationsCountQuery()
            ->where('cg.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        $results = $query->getResult();
        $channelGroups = array_map([$this, 'hydrateRelationsQueryResult'], $results);
        return new ArrayCollection($channelGroups);
    }

    public function hydrateRelationsQueryResult(array $result): IODeviceChannelGroup {
        /** @var IODeviceChannelGroup $channelGroup */
        $channelGroup = $result['channelGroup'];
        unset($result['channelGroup']);
        $channelGroup->setRelationsCount($result);
        return $channelGroup;
    }

    private function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('cg channelGroup')
            ->addSelect('COUNT(c) channels')
            ->addSelect('COUNT(dl) directLinks')
            ->addSelect('COUNT(s) schedules')
            ->from(IODeviceChannelGroup::class, 'cg')
            ->leftJoin('cg.channels', 'c')
            ->leftJoin('cg.directLinks', 'dl')
            ->leftJoin('cg.schedules', 's')
            ->groupBy('cg');
    }
}
