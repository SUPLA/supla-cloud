<?php
namespace SuplaApiBundle\Provider;

use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use OAuth2\Model\IOAuth2Client;
use SuplaApiBundle\Entity\ApiUser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthStorageWithLegacyPasswordSupport extends OAuthStorage {
    /** @var ContainerInterface */
    private $container;

    public function __construct(
        ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthCodeManagerInterface $authCodeManager,
        UserProviderInterface $userProvider = null,
        EncoderFactoryInterface $encoderFactory = null,
        ContainerInterface $container
    ) {
        parent::__construct($clientManager, $accessTokenManager, $refreshTokenManager, $authCodeManager, $userProvider, $encoderFactory);
        $this->container = $container;
    }

    public function checkUserCredentials(IOAuth2Client $client, $username, $plainPassword) {
        $credentialsValid = parent::checkUserCredentials($client, $username, $plainPassword);
        if (!$credentialsValid) {
            $user = $this->getUserIfLegacyPasswordMatches($username, $plainPassword);
            if ($user) {
                $this->rehashLegacyPassword($user, $plainPassword);
                return $this->checkUserCredentials($client, $username, $plainPassword);
            }
        }
        return $credentialsValid;
    }

    /** @return ApiUser|null */
    private function getUserIfLegacyPasswordMatches($username, $plainPassword) {
        try {
            $user = $this->userProvider->loadUserByUsername($username);
            $legacyEncoder = $this->encoderFactory->getEncoder('legacy_encoder');
            if ($user && $legacyEncoder->isPasswordValid($user->getPassword(), $plainPassword, $user->getSalt())) {
                return $user;
            }
        } catch (AuthenticationException $e) {
        }
        return null;
    }

    private function rehashLegacyPassword(ApiUser $user, string $plainPassword) {
        $encoder = $this->encoderFactory->getEncoder($user);
        $hashedPassword = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($hashedPassword);
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();
    }
}
