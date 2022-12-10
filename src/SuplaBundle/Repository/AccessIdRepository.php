<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\ClientApp;

class AccessIdRepository extends EntityWithRelationsRepository {
    protected $alias = 'aid';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('aid entity')
            ->addSelect('COUNT(DISTINCT l) locations')
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s ca WHERE ca.accessId = aid) clientApps', ClientApp::class))
            ->addSelect('supla_is_now_active(aid.activeFrom, aid.activeTo, aid.activeHours, u.timezone) isNowActive')
            ->from(AccessID::class, 'aid')
            ->leftJoin('aid.locations', 'l')
            ->innerJoin('aid.user', 'u')
            ->groupBy('aid.id, u.timezone');
    }

    public function hydrateRelationsQueryResult(array $result) {
        /** @var \SuplaBundle\Entity\Main\AccessID $entity */
        $entity = parent::hydrateRelationsQueryResult($result);
        $entity->setActiveNow(!!$result['isNowActive']);
        return $entity;
    }
}
