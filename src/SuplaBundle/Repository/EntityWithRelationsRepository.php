<?php
namespace SuplaBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class EntityWithRelationsRepository extends EntityRepository {
    protected $alias;

    public function find($id, $lockMode = null, $lockVersion = null) {
        $query = $this->getEntityWithRelationsCountQuery()
            ->where($this->alias . '.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        try {
            $result = $query->getSingleResult();
            $entity = $this->hydrateRelationsQueryResult($result);
        } catch (NoResultException $e) {
            $entity = null;
        }
        return $entity;
    }

    public function findForUser(User $user, int $id) {
        $entity = $this->find($id);
        if (!$entity || !$entity->belongsToUser($user)) {
            throw new NotFoundHttpException("$this->alias ID$id could not be found.");
        }
        return $entity;
    }

    public function findAllForUser(User $user): Collection {
        $query = $this->getEntityWithRelationsCountQuery()
            ->where($this->alias . '.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        $results = $query->getResult();
        $entities = array_map([$this, 'hydrateRelationsQueryResult'], $results);
        return new ArrayCollection($entities);
    }

    public function hydrateRelationsQueryResult(array $result) {
        $entity = $result['entity'];
        unset($result['entity']);
        $entity->setRelationsCount(array_map('intval', $result));
        return $entity;
    }

    abstract protected function getEntityWithRelationsCountQuery(): QueryBuilder;
}
