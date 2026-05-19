<?php
namespace App\Repository;

use App\Entity\Main\PushNotification;
use Doctrine\ORM\QueryBuilder;

class PushNotificationRepository extends EntityWithRelationsRepository {
    protected $alias = 'pn';

    public function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('pn entity')
            ->from(PushNotification::class, 'pn');
    }
}
