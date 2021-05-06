<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization;
use SuplaBundle\Entity\Main\User;

class ApiClientAuthorizationRepository extends EntityRepository {
    /** @return ApiClientAuthorization|null */
    public function findOneByUserAndApiClient(User $user, ApiClient $apiClient) {
        return $this->findBy(['user' => $user, 'apiClient' => $apiClient])[0] ?? null;
    }
}
