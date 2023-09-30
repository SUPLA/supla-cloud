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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\StateWebhookRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StateWebhookController extends RestController {
    use SuplaServerAware;

    /** @var StateWebhookRepository */
    private $stateWebhookRepository;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var bool */
    private $stateWebhooksForPublicAppsOnly;

    public function __construct(
        StateWebhookRepository $stateWebhookRepository,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        TimeProvider $timeProvider,
        bool $stateWebhooksForPublicAppsOnly
    ) {
        $this->stateWebhookRepository = $stateWebhookRepository;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->timeProvider = $timeProvider;
        $this->stateWebhooksForPublicAppsOnly = $stateWebhooksForPublicAppsOnly;
    }

    /**
     * @Put("/integrations/state-webhook")
     * @Security("is_granted('ROLE_STATE_WEBHOOK')")
     */
    public function updateStateWebhookAction(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'functions', 'You have to subscribe for some functions.');
        Assertion::isArray($data['functions'], 'You have to subscribe for some functions.');
        $functions = ChannelFunction::fromStrings($data['functions']);
        Assertion::keyExists($data, 'url', 'url key is missing');
        Assertion::url($data['url']);
        $url = $data['url'];
        Assertion::keyExists($data, 'accessToken', 'accessToken is missing');
        Assertion::string($data['accessToken'], 'accessToken is invalid');
        Assertion::notBlank($data['accessToken'], 'accessToken is invalid');
        $accessToken = $data['accessToken'];
        Assertion::keyExists($data, 'refreshToken', 'refreshToken is missing');
        Assertion::string($data['refreshToken'], 'refreshToken is invalid');
        Assertion::notBlank($data['refreshToken'], 'refreshToken is invalid');
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
        $apiClient = $this->getCurrentApiClient();
        if ($this->stateWebhooksForPublicAppsOnly && !$apiClient->getPublicClientId()) {
            throw new ConflictHttpException('Registering webhooks for non-public API clients is forbidden.');
        }
        $webhook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($apiClient, $this->getCurrentUser());
        $webhook->setUrl($url);
        $webhook->setAccessToken($accessToken);
        $webhook->setRefreshToken($refreshToken);
        $webhook->setExpiresAt(new DateTime('@' . $expiresAt));
        $webhook->setFunctions($functions);
        $webhook->setEnabled(boolval($data['enabled'] ?? true));
        $this->entityManager->persist($webhook);
        $this->entityManager->flush();
        $this->suplaServer->stateWebhookChanged();
        return $this->view($webhook);
    }

    /**
     * @Rest\Delete("/integrations/state-webhook")
     * @Security("is_granted('ROLE_STATE_WEBHOOK')")
     */
    public function deleteStateWebhookAction() {
        $webhook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->getCurrentApiClient(), $this->getCurrentUser());
        if ($webhook->getId()) {
            $this->entityManager->remove($webhook);
            $this->entityManager->flush();
            $this->suplaServer->stateWebhookChanged();
        }
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Get("/integrations/state-webhook")
     * @Security("is_granted('ROLE_STATE_WEBHOOK')")
     */
    public function getStateWebhookAction() {
        $webhook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->getCurrentApiClient(), $this->getCurrentUser());
        if ($webhook->getId()) {
            return $this->view($webhook);
        } else {
            throw new NotFoundHttpException();
        }
    }
}
