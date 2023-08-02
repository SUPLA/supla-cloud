<?php

namespace SuplaBundle\Auth;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\OAuthServerBundle\Entity\AccessTokenManager;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use Symfony\Component\HttpFoundation\RequestStack;

class SuplaAccessTokenManager extends AccessTokenManager {
    /** @var RequestStack */
    private $requestStack;

    public function __construct(ObjectManager $em) {
        parent::__construct($em, AccessToken::class);
    }

    /** @required */
    public function setRequestStack(RequestStack $requestStack): void {
        $this->requestStack = $requestStack;
    }

    public function createToken() {
        return new AccessToken($this->requestStack->getCurrentRequest());
    }
}
