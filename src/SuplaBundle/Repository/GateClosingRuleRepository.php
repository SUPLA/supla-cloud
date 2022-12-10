<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\GateClosingRule;

class GateClosingRuleRepository extends EntityWithRelationsRepository {
    protected $alias = 'gcr';
    protected $pkColumn = 'channel';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('gcr entity')
            ->addSelect('supla_is_now_active(gcr.activeFrom, gcr.activeTo, gcr.activeHours, u.timezone) isNowActive')
            ->from(GateClosingRule::class, 'gcr')
            ->innerJoin('gcr.user', 'u');
    }

    public function hydrateRelationsQueryResult(array $result) {
        /** @var AccessID $entity */
        $entity = parent::hydrateRelationsQueryResult($result);
        $entity->setActiveNow(!!$result['isNowActive']);
        return $entity;
    }
}
