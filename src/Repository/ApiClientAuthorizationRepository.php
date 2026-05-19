<?php
namespace App\Repository;

use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\OAuth\ApiClientAuthorization;
use App\Entity\Main\User;
use Doctrine\ORM\EntityRepository;

class ApiClientAuthorizationRepository extends EntityRepository {
    /** @return ApiClientAuthorization|null */
    public function findOneByUserAndApiClient(User $user, ApiClient $apiClient) {
        return $this->findBy(['user' => $user, 'apiClient' => $apiClient])[0] ?? null;
    }
}
