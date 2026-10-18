<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Controller\Api;

use App\Auth\OAuthAuthorizationCapabilityChecker;
use App\Exception\ApiException;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Dormant backend operation for public-cloud OAuth authorization. The legacy
 * browser login and authorization routes intentionally do not use this API.
 */
class OAuthAuthorizationController {
    private const AUTHORIZATION_FIELDS = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state'];
    private const ALLOWED_FIELDS = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'decision', 'username', 'password'];

    public function __construct(
        private readonly SuplaAutodiscover $autodiscover,
        private readonly TargetSuplaCloudRequestForwarder $requestForwarder,
        private readonly OAuthAuthorizationCapabilityChecker $capabilityChecker,
    ) {
    }

    /** @Route("/oauth/authorize", methods={"POST"}) */
    public function authorizeAction(Request $request): JsonResponse {
        $data = $request->request->all();
        foreach ($data as $name => $value) {
            if (!in_array($name, self::ALLOWED_FIELDS, true) || !is_string($value)) {
                return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
            }
        }
        foreach (['client_id', 'redirect_uri', 'response_type', 'scope', 'decision', 'username', 'password'] as $required) {
            if (!isset($data[$required]) || $data[$required] === '') {
                return $this->error('invalid_request', Response::HTTP_BAD_REQUEST);
            }
        }
        if ($data['decision'] !== 'approve') {
            // This broker operation only forwards fresh-password approval;
            // denial remains local to the existing browser operation.
            return $this->error('invalid_decision', Response::HTTP_BAD_REQUEST);
        }

        if (!$this->autodiscover->enabled() || !$this->autodiscover->isBroker()) {
            return $this->error('target_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            if (!$this->autodiscover->userExists($data['username'])) {
                return $this->error('unknown_target', Response::HTTP_NOT_FOUND);
            }
            $target = $this->autodiscover->getAuthServerForUser($data['username']);
            if ($target->isLocal()) {
                return $this->error('local_authorization_required', Response::HTTP_CONFLICT);
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

        $forwarded = array_intersect_key($data, array_flip(array_merge(self::AUTHORIZATION_FIELDS, ['decision', 'username', 'password'])));
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
}
