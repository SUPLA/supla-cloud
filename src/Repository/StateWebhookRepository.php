<?php
namespace App\Repository;

use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\StateWebhook;
use App\Entity\Main\User;
use Doctrine\ORM\EntityRepository;

class StateWebhookRepository extends EntityRepository {
    public function findOrCreateForApiClientAndUser(?ApiClient $apiClient, User $user): StateWebhook {
        /** @var \App\Entity\Main\StateWebhook $webhook */
        $webhook = $this->findOneBy(['client' => $apiClient, 'user' => $user]);
        return $webhook ?: new StateWebhook($apiClient, $user);
    }
}
