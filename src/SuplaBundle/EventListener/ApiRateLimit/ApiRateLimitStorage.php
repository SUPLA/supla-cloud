<?php

namespace SuplaBundle\EventListener\ApiRateLimit;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use SuplaBundle\Entity\Main\User;

class ApiRateLimitStorage {
    /** @var CacheItemPoolInterface */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache) {
        $this->cache = $cache;
    }

    public function getItem(string $key): CacheItemInterface {
        return $this->cache->getItem($key);
    }

    public function save(CacheItemInterface $item) {
        $this->cache->save($item);
    }

    public function getGlobalKey(): string {
        return 'global';
    }

    public function getUserKey($userOrId) {
        $userId = $userOrId instanceof User ? $userOrId->getId() : $userOrId;
        return 'user_' . $userId;
    }

    public function getDirectLinkCacheKey(int $directLinkId) {
        return 'direct_link_data_' . $directLinkId;
    }

    public function clearUserLimit(User $user) {
        $this->cache->deleteItem($this->getUserKey($user));
    }
}
