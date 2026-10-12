<?php
namespace App\Repository;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use Doctrine\DBAL\LockMode;
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

    /** @return EnergyCostPlanAssignment[] */
    public function findForPlan(EnergyCostPlan $plan, bool $lock = false): array {
        $query = $this->createQueryBuilder('assignment')
            ->where('assignment.energyCostPlan = :plan')
            ->setParameter('plan', $plan)
            ->getQuery();
        if ($lock) {
            // A locking/current read also avoids a stale repeatable-read snapshot after waiting for the plan lock.
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }
        /** @var EnergyCostPlanAssignment[] $assignments */
        $assignments = $query->getResult();
        return $assignments;
    }
}
