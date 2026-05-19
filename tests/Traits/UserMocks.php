<?php

namespace App\Tests\Traits;

use App\Auth\Token\WebappToken;
use App\Entity\Main\User;
use PHPUnit\Framework\MockObject\MockObject;
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
