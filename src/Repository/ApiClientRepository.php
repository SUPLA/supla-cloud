<?php
namespace App\Repository;

use App\Entity\Main\OAuth\ApiClient;
use App\Enums\ApiClientType;
use Assert\Assertion;
use Doctrine\ORM\EntityRepository;

/**
 * @method ApiClient|null findOneByType(int $type)
 */
class ApiClientRepository extends EntityRepository {
    public function getWebappClient(): ApiClient {
        $client = $this->findOneByType(ApiClientType::WEBAPP);
        if (!$client) {
            Assertion::notNull($client, 'You need to create an API Client for webapp. Try executing php bin/console supla:initialize');
        }
        return $client;
    }
}
