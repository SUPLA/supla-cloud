<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\StateWebhook;
use SuplaBundle\Entity\Main\User;

class StateWebhookRepository extends EntityRepository {
    public function findOrCreateForApiClientAndUser(?ApiClient $apiClient, User $user): StateWebhook {
        /** @var \SuplaBundle\Entity\Main\StateWebhook $webhook */
        $webhook = $this->findOneBy(['client' => $apiClient, 'user' => $user]);
        return $webhook ?: new StateWebhook($apiClient, $user);
    }
}
