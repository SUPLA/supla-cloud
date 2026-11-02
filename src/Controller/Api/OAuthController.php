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

namespace App\Controller\Api;

use App\Auth\AutodiscoverPublicClientStub;
use App\Auth\OAuthScope;
use App\Auth\SuplaOAuth2;
use App\Auth\SuplaOAuth2Authenticator;
use App\Entity\Main\OAuth\AccessToken;
use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\OAuth\ApiClientAuthorization;
use App\EventListener\UnavailableInMaintenance;
use App\Model\TimeProvider;
use App\Model\Transactional;
use App\Repository\AccessTokenRepository;
use App\Supla\SuplaAutodiscover;
use App\Supla\SuplaServerAware;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OAuthController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var AccessTokenRepository */
    private $accessTokenRepository;
    /** @var SuplaOAuth2 */
    private $oauthServer;
    /** @var ClientManagerInterface */
    private $clientManager;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        SuplaOAuth2 $oauthServer,
        ClientManagerInterface $clientManager,
        SuplaAutodiscover $autodiscover
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->oauthServer = $oauthServer;
        $this->clientManager = $clientManager;
        $this->autodiscover = $autodiscover;
    }

    /**
     * Read-only OAuth request presentation. This result is never sufficient
     * authority for a later grant or code issuance.
     *
     * @Rest\Get("/oauth/authorization-request")
     */
    public function getAuthorizationRequestAction(Request $request) {
        $query = $request->query->all();
        if ($this->hasDuplicateAuthorizationParameter((string)$request->server->get('QUERY_STRING', ''))) {
            return $this->authorizationRequestError('invalid_request', Response::HTTP_BAD_REQUEST);
        }
        $clientId = $query['client_id'] ?? null;
        if (!is_string($clientId) || $clientId === '') {
            return $this->authorizationRequestError('invalid_client', Response::HTTP_BAD_REQUEST);
        }

        try {
            $client = $this->resolveAuthorizationRequestClient($clientId);
        } catch (\Throwable $e) {
            return $this->authorizationRequestError('metadata_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if (!$client) {
            return $this->authorizationRequestError('invalid_client', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->oauthServer->validateBrowserAuthorizationRequest($request, $client);
        } catch (OAuth2ServerException $e) {
            $error = json_decode($e->getResponseBody(), true)['error'] ?? OAuth2::ERROR_INVALID_REQUEST;
            return $this->authorizationRequestError($error ?: OAuth2::ERROR_INVALID_REQUEST, Response::HTTP_BAD_REQUEST);
        }

        $requestedScope = $query['scope'] ?? '';
        if (!is_string($requestedScope)) {
            return $this->authorizationRequestError('invalid_request', Response::HTTP_BAD_REQUEST);
        }
        $requested = array_values(array_unique(array_filter(explode(' ', $requestedScope), 'strlen')));
        $expanded = (new OAuthScope($requested))->addImplicitScopes();
        $implicitScopes = array_values(array_filter(explode(' ', (string)$expanded), 'strlen'));
        $implicit = array_values(array_diff($implicitScopes, $requested));

        $accessToken = $this->getCurrentAccessToken();
        $user = $accessToken && $accessToken->isForWebapp() ? $accessToken->getUser() : null;
        $status = 'unauthenticated';
        $canAutoAuthorize = false;
        if ($user) {
            $status = 'consent_required';
            // Public catalog stubs are never local grant owners. An existing
            // local client is required before exposing any grant status.
            if (!$client instanceof AutodiscoverPublicClientStub) {
                $authorization = $this->entityManager->getRepository(ApiClientAuthorization::class)
                    ->findOneByUserAndApiClient($user, $client);
                if ($authorization && $authorization->isAuthorized($expanded)) {
                    $status = 'already_authorized';
                    $canAutoAuthorize = true;
                }
            }
        }

        $result = [
            'application' => [
                'name' => $client->getName(),
                'description' => $client->getDescription(),
            ],
            'scopes' => ['requested' => $requested, 'implicit' => $implicit],
            'status' => $status,
            'canAutoAuthorize' => $canAutoAuthorize,
        ];
        if ($user) {
            $result['email'] = $user->getEmail();
        }
        return $this->json($result, Response::HTTP_OK, ['Cache-Control' => 'no-store']);
    }

    private function resolveAuthorizationRequestClient(string $clientId): ?ApiClient {
        $client = $this->clientManager->findClientByPublicId($clientId);
        if (!$this->autodiscover->enabled()) {
            return $client;
        }
        $publicId = $client ? $client->getPublicClientId() : $clientId;
        $data = $publicId ? $this->autodiscover->getPublicClient($publicId) : null;
        if (!$client && !$data && $this->autodiscover->isTarget()) {
            $publicId = $this->autodiscover->getPublicIdBasedOnMappedId($clientId);
            $data = $publicId ? $this->autodiscover->getPublicClient($publicId) : null;
        }
        if ($data) {
            // When a Browser WebApp token routes this read to its Target
            // Cloud, the request may still carry the public identifier. Match
            // an existing mapped local client without creating or mutating it.
            if (!$client && isset($data['clientId'])) {
                $client = $this->clientManager->findClientBy(['publicClientId' => $data['clientId']]);
            }
            $client = $client ? clone $client : new AutodiscoverPublicClientStub($clientId);
            $client->updateDataFromAutodiscover($data);
        }
        return $client;
    }

    private function authorizationRequestError(string $code, int $status) {
        return $this->json(['error' => $code], $status, ['Cache-Control' => 'no-store']);
    }

    private function hasDuplicateAuthorizationParameter(string $query): bool {
        $oauthParameters = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];
        $seen = [];
        foreach (explode('&', $query) as $parameter) {
            [$name] = explode('=', $parameter, 2);
            $name = urldecode($name);
            if (in_array($name, $oauthParameters, true)) {
                if (isset($seen[$name])) {
                    return true;
                }
                $seen[$name] = true;
            }
        }
        return false;
    }

    /**
     * @Security("is_granted('ROLE_WEBAPP')")
     * @Rest\Get("/oauth-clients")
     */
    public function getOAuthClientsAction(Request $request) {
        $applications = $this->getUser()->getApiClients();
        return $this->serializedView($applications, $request);
    }

    /**
     * @Security("client.belongsToUser(user) and is_granted('ROLE_WEBAPP')")
     * @Rest\Get("/oauth-clients/{client}")
     */
    public function getOAuthClientAction(ApiClient $client, Request $request) {
        $view = $this->view($client, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['secret']);
        return $view;
    }

    /**
     * @Security("is_granted('ROLE_WEBAPP')")
     * @Rest\Post("/oauth-clients")
     * @UnavailableInMaintenance
     */
    public function postOAuthClientsAction(ApiClient $newClient, Request $request) {
        $user = $this->getUser();
        Assertion::false($user->isLimitOAuthClientExceeded(), 'OAuth clients limit has been exceeded');
        $newClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $newClient->setUser($user);
        $this->clientManager->updateClient($newClient);
        return $this->getOAuthClientAction($newClient, $request);
    }

    /**
     * @Security("client.belongsToUser(user) && is_granted('ROLE_WEBAPP')")
     * @Rest\Put("/oauth-clients/{client}")
     * @UnavailableInMaintenance
     */
    public function putOAuthClientAction(ApiClient $client, ApiClient $updatedClient, Request $request) {
        $client->setName($updatedClient->getName());
        $client->setDescription($updatedClient->getDescription());
        $client->setRedirectUris($updatedClient->getRedirectUris());
        $this->clientManager->updateClient($client);
        return $this->getOAuthClientAction($client, $request);
    }

    /**
     * @Security("client.belongsToUser(user) and is_granted('ROLE_WEBAPP')")
     * @Rest\Delete("/oauth-clients/{client}")
     * @UnavailableInMaintenance
     */
    public function deleteOAuthClientAction(ApiClient $client) {
        return $this->transactional(function (EntityManagerInterface $em) use ($client) {
            $em->remove($client);
            $this->suplaServer->onOAuthClientRemoved();
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/oauth-authorized-clients")
     * @Security("is_granted('ROLE_WEBAPP')")
     */
    public function getAuthorizedClientsAction(Request $request) {
        $apps = $this->getUser()->getApiClientAuthorizations();
        $view = $this->view($apps, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['client']);
        return $view;
    }

    /**
     * @Rest\Delete("/oauth-authorized-clients/{authorizedApp}")
     * @Security("authorizedApp.belongsToUser(user) and is_granted('ROLE_WEBAPP')")
     * @UnavailableInMaintenance
     */
    public function deleteAuthorizedClientsAction(ApiClientAuthorization $authorizedApp, Request $request) {
        return $this->transactional(function (EntityManagerInterface $em) use ($authorizedApp) {
            $em->remove($authorizedApp);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/oauth-personal-tokens")
     * @Security("is_granted('ROLE_WEBAPP')")
     */
    public function getPersonalTokensAction(Request $request) {
        $accessTokens = $this->accessTokenRepository->findPersonalTokens($this->getUser());
        return $this->serializedView($accessTokens, $request);
    }

    /**
     * @Rest\Post("/oauth-personal-tokens")
     * @Security("is_granted('ROLE_WEBAPP')")
     * @UnavailableInMaintenance
     */
    public function postPersonalTokensAction(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'name');
        Assertion::keyExists($data, 'scope');
        Assertion::notBlank(
            $data['name'],
            'Personal token name is required.' // i18n
        );
        $scope = (new OAuthScope($data['scope']))->removeNonWebappScopes();
        Assertion::false($scope->isEmpty(), 'You have to choose at least one scope.'); // i18n
        $token = $this->transactional(function (EntityManagerInterface $entityManager) use ($data, $scope) {
            $token = $this->oauthServer->createPersonalAccessToken($this->getUser(), $data['name'], $scope);
            $entityManager->persist($token);
            return $token;
        });
        return $this->serializedView($token, $request, ['token'], Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete("/oauth-personal-tokens/{accessToken}", name="delete_personal_token")
     * @Rest\Delete("/access-tokens/{accessToken}", name="delete_access_token")
     * @Security("accessToken.belongsToUser(user) and is_granted('ROLE_WEBAPP')")
     * @UnavailableInMaintenance
     */
    public function deletePersonalTokenAction(AccessToken $accessToken) {
        return $this->transactional(function (EntityManagerInterface $em) use ($accessToken) {
            $em->remove($accessToken);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Security("is_granted('ROLE_WEBAPP')")
     * @Rest\Post("/logout")
     */
    public function logoutAction() {
        return $this->transactional(function (EntityManagerInterface $em) {
            $token = $this->getCurrentUserToken();
            $accessToken = $token->getAttribute(SuplaOAuth2Authenticator::REQUEST_ATTRIBUTE_ACCESS_TOKEN);
            if ($accessToken) {
                $em->remove($accessToken);
            }
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/public-oauth-apps")
     */
    public function getPublicOAuthAppsAction() {
        if ($this->autodiscover->isTarget()) {
            $clients = $this->autodiscover->getPublicClients();
            $nonHiddenClients = array_filter($clients, function ($client) {
                return !($client['isHidden'] ?? false);
            });
            return $this->view(array_values($nonHiddenClients), Response::HTTP_OK);
        } else {
            return new Response('', Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * @Security("is_granted('ROLE_WEBAPP')")
     * @Rest\Get("/access-tokens")
     */
    public function getAccessTokensAction(Request $request, TimeProvider $timeProvider) {
        $accessTokens = $this->accessTokenRepository->createQueryBuilder('at')
            ->where('at.user = :user')
            ->andWhere('at.expiresAt IS NOT NULL AND at.expiresAt > :obsoleteDate')
            ->andWhere("at.scope != 'channels_files'")
            ->orderBy('at.expiresAt', 'DESC')
            ->setParameter('user', $this->getUser())
            ->setParameter('obsoleteDate', $timeProvider->getTimestamp())
            ->getQuery()
            ->getResult();
        return $this->serializedView($accessTokens, $request, ['issuer', 'client']);
    }
}
