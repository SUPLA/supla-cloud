<?php
namespace App\Repository;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\User;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<EnergyCostPlan> */
class EnergyCostPlanRepository extends EntityRepository {
    /** @return EnergyCostPlan[] */
    public function findByUser(User $user): array {
        return $this->findBy(['user' => $user], ['name' => 'ASC', 'id' => 'ASC']);
    }

    public function findOneByIdAndUser(int|string $id, User $user): ?EnergyCostPlan {
        /** @var EnergyCostPlan|null $plan */
        $plan = $this->findOneBy(['id' => $id, 'user' => $user]);
        return $plan;
    }
}
