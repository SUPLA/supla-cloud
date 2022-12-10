<?php
namespace SuplaBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository {
    /** @return \SuplaBundle\Entity\Main\Schedule[] */
    public function findByQuery(ScheduleListQuery $query): array {
        $criteria = Criteria::create();
        if ($query->getUser()) {
            $criteria->where(Criteria::expr()->eq('user', $query->getUser()));
        }
        if ($query->getChannel()) {
            $criteria->where(Criteria::expr()->eq('channel', $query->getChannel()));
        }
        if ($query->getChannelGroup()) {
            $criteria->where(Criteria::expr()->eq('channelGroup', $query->getChannelGroup()));
        }
        if ($query->getScene()) {
            $criteria->where(Criteria::expr()->eq('scene', $query->getScene()));
        }
        if ($query->getOrderBy()) {
            $criteria->orderBy($query->getOrderBy());
        }
        return $this->matching($criteria)->toArray();
    }
}
