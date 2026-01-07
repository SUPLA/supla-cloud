<?php

namespace SuplaBundle\Tests\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use SuplaBundle\Auth\Token\WebappToken;
use SuplaBundle\Entity\Main\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @method MockObject createMock(string $className)
 */
trait UserMocks {
    protected function getMockedTokenStorage(?User $user = null): TokenStorageInterface {
        $user = $user ?: $this->createMock(User::class);
        $token = $this->createMock(WebappToken::class);
        $token->method('getUser')->willReturn($user);
        $userTokenStorage = $this->createMock(TokenStorageInterface::class);
        $userTokenStorage->method('getToken')->willReturn($token);
        return $userTokenStorage;
    }
}
