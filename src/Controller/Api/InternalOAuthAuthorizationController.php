<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Controller\Api;

use App\Auth\OAuthAuthorizationGrantService;
use App\Auth\OAuthScope;
use App\Auth\SuplaOAuth2;
use App\Auth\SuplaOAuthStorage;
use App\Auth\Voter\BrokerRequestSecurityVoter;
use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\User;
use App\Exception\ApiException;
use Doctrine\DBAL\Exception as StorageException;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2RedirectException;
use OAuth2\OAuth2ServerException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class InternalOAuthAuthorizationController {
    private const AUTHORIZATION_PARAMETERS = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];

    public function __construct(
        private readonly BrokerRequestSecurityVoter $brokerRequestSecurityVoter,
        private readonly SuplaOAuthStorage $oauthStorage,
        private readonly ClientManagerInterface $clientManager,
        private readonly SuplaOAuth2 $oauth2Server,
        private readonly OAuthAuthorizationGrantService $authorizationGrantService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * This endpoint is deliberately public at the firewall layer so a trusted
     * broker can call it without an end-user bearer token. Access is instead
     * restricted to the existing broker-token verifier below.
     *
     * @Route("/internal/oauth/authorize", methods={"POST"})
     */
    public function authorizeAction(Request $request): JsonResponse {
        try {
            $verifiedBroker = $this->brokerRequestSecurityVoter->isRequestFromBroker($request);
        } catch (ApiException $e) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if (!$verifiedBroker) {
            return $this->error('unauthorized_broker', Response::HTTP_UNAUTHORIZED);
        }

        $data = $request->request->all();
        $allowedParameters = array_merge(self::AUTHORIZATION_PARAMETERS, ['decision', 'username', 'password']);
        foreach ($data as $name => $value) {
            if (!in_array($name, $allowedParameters, true) || !is_string($value)) {
                return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
            }
        }
        $authorizationRequest = array_intersect_key($data, array_flip(self::AUTHORIZATION_PARAMETERS));
        $request->query->replace($authorizationRequest);

        $client = $this->clientManager->findClientByPublicId($authorizationRequest['client_id'] ?? '');
        if (!$client instanceof ApiClient) {
            return $this->error('invalid_client', Response::HTTP_BAD_REQUEST);
        }

        // oauth2-php 1.10 accepts URI prefixes (and sanitizes input). The new
        // contract requires the original, entire registered URI before even a
        // protocol error may redirect. Leave legacy browser matching unchanged.
        $redirectUri = $authorizationRequest['redirect_uri'] ?? null;
        if (
            !is_string($redirectUri) || !in_array($redirectUri, $client->getRedirectUris(), true)
            || preg_match('/[\x00-\x20\x7f]/', $redirectUri) || str_contains($redirectUri, '#')
        ) {
            return $this->error('redirect_uri_mismatch', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->oauth2Server->validateAuthorizationRequest($request);
            if (($authorizationRequest['response_type'] ?? null) !== OAuth2::RESPONSE_TYPE_AUTH_CODE) {
                throw new OAuth2RedirectException($redirectUri, OAuth2::ERROR_UNSUPPORTED_RESPONSE_TYPE, null, $authorizationRequest['state'] ?? null);
            }
            if (!$this->oauthStorage->checkRestrictedGrantType($client, OAuth2::GRANT_TYPE_AUTH_CODE)) {
                throw new OAuth2RedirectException($redirectUri, OAuth2::ERROR_UNAUTHORIZED_CLIENT, null, $authorizationRequest['state'] ?? null);
            }
            // FOS also knows the retired restapi scope; consent entities accept
            // only currently supported SUPLA scopes. Reject before any writes.
            $scope = new OAuthScope($authorizationRequest['scope'] ?? '');
            try {
                $scope->ensureThatAllScopesAreSupported();
                $supportedScope = !$scope->isEmpty();
            } catch (ApiException $e) {
                $supportedScope = false;
            }
            if (!$supportedScope) {
                throw new OAuth2RedirectException($redirectUri, OAuth2::ERROR_INVALID_SCOPE, null, $authorizationRequest['state'] ?? null);
            }
        } catch (OAuth2ServerException $e) {
            return $this->oauthError($e, $request);
        }

        if (!in_array($data['decision'] ?? null, ['approve', 'deny'], true)) {
            return $this->error('invalid_decision', Response::HTTP_BAD_REQUEST);
        }

        if ($data['decision'] === 'deny') {
            try {
                $this->oauth2Server->finishClientAuthorization(false, null, $request);
            } catch (OAuth2RedirectException $e) {
                return $this->response('denied', $this->redirectUrl($e, $request));
            }
        }

        $user = $this->authenticateUser($client, $data['username'] ?? null, $data['password'] ?? null);
        if (!$user instanceof User) {
            return $user;
        }

        try {
            $response = $this->authorizationGrantService->authorize(
                $user,
                $client,
                $authorizationRequest['scope'] ?? '',
                fn() => $this->oauth2Server->finishClientAuthorization(true, $user, $request, $authorizationRequest['scope'] ?? null)
            );
            return $this->response('authorized', $response->headers->get('Location'));
        } catch (OAuth2ServerException $e) {
            return $this->oauthError($e, $request);
        } catch (StorageException $e) {
            // The grant service has rolled back consent and code. Do not expose
            // SQL, credentials or exception arguments in the result or logs.
            $this->logger->error('Internal OAuth authorization storage failed.', ['exceptionType' => get_class($e)]);
            return $this->error('server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function oauthError(OAuth2ServerException $exception, Request $request): JsonResponse {
        $error = json_decode($exception->getResponseBody(), true)['error'];
        $response = ['outcome' => 'error', 'errorCode' => $error];
        if ($exception instanceof OAuth2RedirectException) {
            $response['redirectUrl'] = $this->redirectUrl($exception, $request);
        }
        return new JsonResponse($response, Response::HTTP_BAD_REQUEST, ['Cache-Control' => 'no-store']);
    }

    private function redirectUrl(OAuth2RedirectException $exception, Request $request): string {
        $url = $exception->getResponseHeaders()['Location'];
        // oauth2-php 1.10 drops falsey states in error redirects. The callback
        // has already been validated and fragments rejected above.
        if ($request->query->has('state') && !$request->query->get('state')) {
            $url .= '&state=' . rawurlencode($request->query->get('state'));
        }
        return $url;
    }

    /** @return User|JsonResponse */
    private function authenticateUser(ApiClient $client, ?string $username, ?string $password) {
        if (!$username || !$password || preg_match('/^api_[0-9]+$/', $username)) {
            return $this->error('invalid_credentials', Response::HTTP_UNAUTHORIZED);
        }
        try {
            $user = $this->oauthStorage->authenticateAuthorizationUser($client, $username, $password);
        } catch (LockedException $e) {
            return $this->error('locked', Response::HTTP_TOO_MANY_REQUESTS);
        } catch (DisabledException $e) {
            return $this->error('disabled', Response::HTTP_CONFLICT);
        } catch (UsernameNotFoundException $e) {
            return $this->error('invalid_credentials', Response::HTTP_UNAUTHORIZED);
        }
        if (!$user instanceof User) {
            return $this->error('invalid_credentials', Response::HTTP_UNAUTHORIZED);
        }
        return $user;
    }

    private function response(string $outcome, ?string $redirectUrl): JsonResponse {
        return new JsonResponse(['outcome' => $outcome, 'redirectUrl' => $redirectUrl], Response::HTTP_OK, [
            'Cache-Control' => 'no-store',
        ]);
    }

    private function error(string $errorCode, int $status): JsonResponse {
        return new JsonResponse(['outcome' => 'error', 'errorCode' => $errorCode], $status, ['Cache-Control' => 'no-store']);
    }
}
