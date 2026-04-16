<?php

namespace SuplaBundle\Auth;

use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Entity\AccessTokenManager;
use FOS\OAuthServerBundle\Model\TokenInterface;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use Symfony\Component\HttpFoundation\RequestStack;

class SuplaAccessTokenManager extends AccessTokenManager {
    /** @var RequestStack */
    private $requestStack;

    public function __construct(EntityManagerInterface $em) {
        parent::__construct($em, AccessToken::class);
    }

    /** @required */
    public function setRequestStack(RequestStack $requestStack): void {
        $this->requestStack = $requestStack;
    }

    public function createToken(): TokenInterface {
        return new AccessToken($this->requestStack->getCurrentRequest());
    }
}
