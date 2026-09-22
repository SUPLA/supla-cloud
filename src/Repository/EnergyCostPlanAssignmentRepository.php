<?php
namespace App\Repository;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<EnergyCostPlanAssignment> */
class EnergyCostPlanAssignmentRepository extends EntityRepository {
    public function findForChannel(IODeviceChannel $channel): ?EnergyCostPlanAssignment {
        /** @var EnergyCostPlanAssignment|null $assignment */
        $assignment = $this->findOneBy(['channel' => $channel]);
        return $assignment;
    }

    public function findPlanForChannel(IODeviceChannel $channel): ?EnergyCostPlan {
        /** @var EnergyCostPlanAssignment|null $assignment */
        $assignment = $this->createQueryBuilder('assignment')
            ->addSelect('plan')
            ->innerJoin('assignment.energyCostPlan', 'plan')
            ->where('assignment.channel = :channel')
            ->setParameter('channel', $channel)
            ->getQuery()
            ->getOneOrNullResult();
        return $assignment?->getEnergyCostPlan();
    }
}
