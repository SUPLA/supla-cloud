<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\StateWebhook;
use SuplaBundle\Entity\User;

class StateWebhookRepository extends EntityRepository {
    public function findOrCreateForApiClientAndUser(ApiClient $apiClient, User $user): StateWebhook {
        /** @var StateWebhook $webhook */
        $webhook = $this->findOneBy(['client' => $apiClient, 'user' => $user]);
        return $webhook ?: new StateWebhook($apiClient, $user);
    }
}
