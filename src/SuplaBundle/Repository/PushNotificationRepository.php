<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Main\PushNotification;

class PushNotificationRepository extends EntityWithRelationsRepository {
    protected $alias = 'pn';

    public function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('pn entity')
            ->from(PushNotification::class, 'pn');
    }
}
