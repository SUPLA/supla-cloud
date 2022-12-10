<?php
namespace SuplaBundle\Repository;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\HasRelationsCount;
use SuplaBundle\Entity\Main\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class EntityWithRelationsRepository extends EntityRepository {
    protected $alias;
    protected $pkColumn = 'id';

    public function find($id, $lockMode = null, $lockVersion = null) {
        if (is_array($id)) {
            $id = $id['id'] ?? null;
            Assertion::notNull($id, 'Invalid ID given.');
        }
        $query = $this->getEntityWithRelationsCountQuery()
            ->where($this->alias . ".$this->pkColumn = :id")
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

    public function findForUserOrNull(User $user, int $id) {
        try {
            return $this->findForUser($user, $id);
        } catch (NotFoundHttpException $e) {
            return null;
        }
    }

    public function findAllForUser(User $user, callable $additionalConditions = null): Collection {
        $query = $this->getEntityWithRelationsCountQuery()
            ->where($this->alias . '.user = :user')
            ->setParameter('user', $user);
        if ($additionalConditions) {
            $additionalConditions($query, $this->alias);
        }
        $results = $query->getQuery()->getResult();
        $entities = array_map([$this, 'hydrateRelationsQueryResult'], $results);
        return new ArrayCollection($entities);
    }

    public function hydrateRelationsQueryResult(array $result) {
        $entity = $result['entity'];
        unset($result['entity']);
        if ($entity instanceof HasRelationsCount) {
            $entity->setRelationsCount(array_map('intval', $result));
        }
        return $entity;
    }

    abstract protected function getEntityWithRelationsCountQuery(): QueryBuilder;
}
