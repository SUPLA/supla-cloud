<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaApiBundle\Entity\OAuth\ApiClient;
use SuplaApiBundle\Entity\OAuth\ApiClientAuthorization;
use SuplaBundle\Entity\User;

class ApiClientAuthorizationRepository extends EntityRepository {
    /** @return ApiClientAuthorization|null */
    public function findOneByUserAndApiClient(User $user, ApiClient $apiClient) {
        return $this->findBy(['user' => $user, 'apiClient' => $apiClient])[0] ?? null;
    }
}
