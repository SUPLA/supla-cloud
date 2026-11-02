<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Controller\OAuth;

use App\Auth\AutodiscoverPublicClientStub;
use App\Auth\OAuthAuthorizationCapabilityChecker;
use App\Auth\MappedApiClientResolver;
use App\Auth\SuplaOAuth2;
use App\Controller\Api\InternalOAuthAuthorizationController;
use App\Entity\Main\OAuth\ApiClient;
use App\Exception\ApiException;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Supla\SuplaAutodiscover;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2RedirectException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Browser-facing OAuth consent operation. The broker owns discovery and safe
 * forwarding; the account-owning Target Cloud owns grants and authorization
 * codes.
 *
 * The route is annotated with its full path (like BrokerTokenController)
 * instead of relying on the versioned /api/{version} prefix import: the
 * prefixed variant would shadow /api/{version}/internal/oauth/authorize by
 * matching {version}=internal.
 */
class OAuthAuthorizationController {
    private const AUTHORIZATION_FIELDS = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];
    private const ALLOWED_FIELDS = [
        'client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method',
        'decision', 'username', 'password', 'target_token',
    ];

    public function __construct(
        private readonly SuplaAutodiscover $autodiscover,
        private readonly TargetSuplaCloudRequestForwarder $requestForwarder,
        private readonly OAuthAuthorizationCapabilityChecker $capabilityChecker,
        private readonly InternalOAuthAuthorizationController $localAuthorizationController,
        private readonly ClientManagerInterface $clientManager,
        private readonly MappedApiClientResolver $mappedClientResolver,
        private readonly SuplaOAuth2 $oauth2Server,
    ) {
    }

    /** @Route("/api/oauth/authorize", methods={"POST"}) */
    public function authorizeAction(Request $request): JsonResponse {
        $data = $request->request->all();
        foreach ($data as $name => $value) {
            if (!in_array($name, self::ALLOWED_FIELDS, true) || !is_string($value)) {
                return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
            }
        }
        foreach (['client_id', 'redirect_uri', 'response_type', 'scope', 'decision'] as $required) {
            if (!isset($data[$required]) || $data[$required] === '') {
                return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
            }
        }
        if (!in_array($data['decision'], ['approve', 'deny'], true)) {
            return $this->error('invalid_decision', Response::HTTP_BAD_REQUEST);
        }
        if ($data['decision'] === 'deny' && (!isset($data['username']) || $data['username'] === '')) {
            return $this->denyAuthorization($request, $data);
        }
        if ($data['decision'] === 'approve' && (!isset($data['username']) || $data['username'] === '')) {
            return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
        }
        $hasTargetToken = array_key_exists('target_token', $data);
        if (
            $data['decision'] === 'approve' && (
            ($hasTargetToken && ($data['target_token'] === '' || ($data['password'] ?? '') !== ''))
            || (!$hasTargetToken && (!isset($data['password']) || $data['password'] === ''))
            )
        ) {
            return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
        }

        if (!$this->autodiscover->enabled() || !$this->autodiscover->isBroker()) {
            // Standalone and target Clouds own their local registrations and
            // users. They do not need broker discovery to authorize locally.
            return $this->localAuthorizationController->authorizeLocalBrowserRequest($request);
        }

        try {
            if (!$this->autodiscover->userExists($data['username'])) {
                return $this->error('unknown_target', Response::HTTP_NOT_FOUND);
            }
            $target = $this->autodiscover->getAuthServerForUser($data['username']);
            if ($target->isLocal()) {
                // Public IDs are mapped on the owning instance; local/private
                // registered client IDs pass through unchanged.
                $mappedClientId = $this->autodiscover->getTargetCloudClientId($target, $data['client_id']);
                if ($mappedClientId) {
                    $request->request->set('client_id', $mappedClientId);
                }
                return $this->localAuthorizationController->authorizeLocalBrowserRequest($request);
            }
            $mappedClientId = $this->autodiscover->getTargetCloudClientId($target, $data['client_id']);
        } catch (ApiException | \InvalidArgumentException $e) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if (!$mappedClientId) {
            return $this->error('invalid_client', Response::HTTP_BAD_REQUEST);
        }

        try {
            $capability = $this->capabilityChecker->check($target);
        } catch (\InvalidArgumentException $e) {
            // A destination not established by AD is a programming/trust
            // boundary violation; never attempt a fallback request.
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if ($capability->getStatus() === 'unsupported_authorization_api') {
            return $this->error('unsupported_authorization_api', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if (!$capability->isSupported()) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $forwarded = array_intersect_key($data, array_flip(array_merge(self::AUTHORIZATION_FIELDS, ['decision', 'username', 'password', 'target_token'])));
        $forwarded['client_id'] = $mappedClientId;
        try {
            [$body, $status] = $this->requestForwarder->authorizeOAuth($target, $forwarded);
        } catch (ApiException $e) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\InvalidArgumentException $e) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $result = is_array($body) ? $body : json_decode((string) $body, true);
        if (!is_array($result) || !isset($result['outcome']) || !in_array($result['outcome'], ['authorized', 'denied', 'error'], true)) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        // Do not reinterpret or synthesize target callbacks. The target owns
        // callback validation, grant persistence, and authorization-code issue.
        return new JsonResponse($result, $status >= 200 && $status < 600 ? $status : Response::HTTP_SERVICE_UNAVAILABLE, [
            'Cache-Control' => 'no-store',
        ]);
    }

    private function error(string $code, int $status): JsonResponse {
        return new JsonResponse(['outcome' => 'error', 'errorCode' => $code], $status, ['Cache-Control' => 'no-store']);
    }

    /** @param array<string, string> $data */
    private function denyAuthorization(Request $request, array $data): JsonResponse {
        try {
            $client = $this->clientManager->findClientByPublicId($data['client_id']);
            if (!$client instanceof ApiClient) {
                $client = null;
            }
            if (!$client && $this->autodiscover->isTarget()) {
                $client = $this->mappedClientResolver->resolve($data['client_id']);
            }
            if (!$client && $this->autodiscover->enabled()) {
                $publicId = $data['client_id'];
                $publicClient = $this->autodiscover->getPublicClient($publicId);
                if (!$publicClient && $this->autodiscover->isTarget()) {
                    $publicId = $this->autodiscover->getPublicIdBasedOnMappedId($data['client_id']);
                    $publicClient = $publicId ? $this->autodiscover->getPublicClient($publicId) : null;
                }
                if ($publicClient) {
                    $client = new AutodiscoverPublicClientStub($data['client_id']);
                    $client->updateDataFromAutodiscover($publicClient);
                }
            }
        } catch (ApiException | \InvalidArgumentException $e) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        if (!$client instanceof ApiClient) {
            return $this->error('invalid_client', Response::HTTP_BAD_REQUEST);
        }

        $authorizationRequest = array_intersect_key($data, array_flip(self::AUTHORIZATION_FIELDS));
        $request->query->replace($authorizationRequest);
        try {
            $redirectUrl = $this->oauth2Server->getDeniedAuthorizationRedirect($request, $client);
            return new JsonResponse(
                ['outcome' => 'denied', 'redirectUrl' => $redirectUrl],
                Response::HTTP_OK,
                ['Cache-Control' => 'no-store']
            );
        } catch (OAuth2RedirectException $e) {
            $error = json_decode($e->getResponseBody(), true)['error'] ?? 'invalid_request';
            $location = $e->getResponseHeaders()['Location'] ?? null;
            return new JsonResponse(
                ['outcome' => 'error', 'errorCode' => $error, 'redirectUrl' => $location],
                Response::HTTP_BAD_REQUEST,
                ['Cache-Control' => 'no-store']
            );
        } catch (OAuth2ServerException $e) {
            $error = json_decode($e->getResponseBody(), true)['error'] ?? 'invalid_request';
            return $this->error($error, Response::HTTP_BAD_REQUEST);
        }
    }
}
