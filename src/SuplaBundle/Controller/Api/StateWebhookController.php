<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Controller\Api;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\SuplaOAuthStorage;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\StateWebhookRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StateWebhookController extends RestController {
    use SuplaServerAware;

    /** @var StateWebhookRepository */
    private $stateWebhookRepository;
    /** @var OAuthStorage */
    private $oAuthStorage;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var TimeProvider */
    private $timeProvider;

    public function __construct(
        StateWebhookRepository $stateWebhookRepository,
        TokenStorageInterface $tokenStorage,
        SuplaOAuthStorage $oAuthStorage,
        EntityManagerInterface $entityManager,
        TimeProvider $timeProvider
    ) {
        $this->stateWebhookRepository = $stateWebhookRepository;
        $this->tokenStorage = $tokenStorage;
        $this->oAuthStorage = $oAuthStorage;
        $this->entityManager = $entityManager;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @Put("/integrations/state-webhook")
     * @Security("has_role('ROLE_STATE_WEBHOOK')")
     */
    public function updateStateWebhookAction(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'functions', 'You have to subscribe for some functions.');
        Assertion::isArray($data['functions'], 'You have to subscribe for some functions.');
        $functions = ChannelFunction::fromStrings($data['functions']);
        Assertion::keyExists($data, 'url', 'url key is missing');
        Assertion::url($data['url']);
        $url = $data['url'];
        Assertion::keyExists($data, 'authToken', 'authToken is missing');
        Assertion::string($data['authToken'], 'authToken is invalid');
        Assertion::notBlank($data['authToken'], 'authToken is invalid');
        $authToken = $data['authToken'];
        Assertion::keyExists($data, 'refreshToken', 'refreshToken is missing');
        Assertion::string($data['refreshToken'], 'authToken is invalid');
        Assertion::notBlank($data['refreshToken'], 'authToken is invalid');
        $refreshToken = $data['refreshToken'];
        Assertion::keyExists($data, 'expiresAt', 'expiresAt is missing');
        Assertion::integer($data['expiresAt'], 'expiresAt should be a timestamp');
        Assertion::between(
            $data['expiresAt'],
            $this->timeProvider->getTimestamp('+1 day'),
            $this->timeProvider->getTimestamp('+90 days'),
            'expiresAt should be between 24 hours and 90 days from now'
        );
        $expiresAt = $data['expiresAt'];
        $webhook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->getCurrentApiClient(), $this->getCurrentUser());
        $webhook->setUrl($url);
        $webhook->setAuthToken($authToken);
        $webhook->setRefreshToken($refreshToken);
        $webhook->setExpiresAt(new \DateTime('@' . $expiresAt));
        $webhook->setFunctions($functions);
        $webhook->setEnabled(boolval($data['enabled'] ?? true));
        $this->entityManager->persist($webhook);
        $this->entityManager->flush();
        return $this->view($webhook);
    }

    /**
     * @Rest\Delete("/integrations/state-webhook")
     * @Security("has_role('ROLE_STATE_WEBHOOK')")
     */
    public function deleteStateWebhookAction() {
        $webhook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->getCurrentApiClient(), $this->getCurrentUser());
        if ($webhook->getId()) {
            $this->entityManager->remove($webhook);
            $this->entityManager->flush();
        }
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Get("/integrations/state-webhook")
     * @Security("has_role('ROLE_STATE_WEBHOOK')")
     */
    public function getStateWebhookAction() {
        $webhook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->getCurrentApiClient(), $this->getCurrentUser());
        if ($webhook->getId()) {
            return $this->view($webhook);
        } else {
            throw new NotFoundHttpException();
        }
    }

    private function getCurrentApiClient(): ApiClient {
        /** @var OAuthToken $token */
        $token = $this->tokenStorage->getToken();
        /** @var AccessToken $accessToken */
        $accessToken = $this->oAuthStorage->getAccessToken($token->getToken());
        return $accessToken->getClient();
    }
}
