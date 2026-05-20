<?php

namespace App\Tests\Traits;

use App\Entity\Main\User;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

/**
 * @method MockObject createMock(string $className)
 */
trait UserMocks {
    protected function getMockedTokenStorage(?User $user = null): TokenStorageInterface {
        $user = $user ?: $this->createMock(User::class);
        $token = $this->createMock(PostAuthenticationToken::class);
        $token->method('getUser')->willReturn($user);
        $userTokenStorage = $this->createMock(TokenStorageInterface::class);
        $userTokenStorage->method('getToken')->willReturn($token);
        return $userTokenStorage;
    }
}
