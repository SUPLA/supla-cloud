<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Auth;

use App\Entity\Main\OAuth\ApiClient;
use App\Enums\ApiClientType;
use App\Exception\ApiException;
use App\Supla\SuplaAutodiscover;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

/**
 * Resolves an OAuth client identifier to a target-local {@see ApiClient} for
 * the internal authorization path.
 *
 * A client already registered locally (including non-public local clients and
 * previously provisioned mapped clients) is returned only after refreshing
 * mapped public metadata from Autodiscover. A public
 * Autodiscover application that is mapped to this target but has not been
 * materialized locally yet is provisioned on first use, replicating the
 * legacy first-use mapping semantics of the browser OAuth flow: the mapped id
 * is looked up in Autodiscover, the public client metadata is fetched, a
 * target-local BROKER client is created from it, and the new credentials are
 * synchronized back to Autodiscover.
 */
class MappedApiClientResolver {
    public function __construct(
        private readonly ClientManagerInterface $clientManager,
        private readonly SuplaAutodiscover $autodiscover,
    ) {
    }

    /**
     * @throws ApiException when Autodiscover cannot be reached while resolving
     *                      a mapped public client.
     */
    public function resolve(?string $clientId): ?ApiClient {
        if ($clientId === null || $clientId === '') {
            return null;
        }
        $client = $this->clientManager->findClientByPublicId($clientId);
        if ($client instanceof ApiClient) {
            return $this->refreshMappedClient($client);
        }
        return $this->provisionMappedPublicClient($clientId);
    }

    private function refreshMappedClient(ApiClient $client): ?ApiClient {
        $publicId = $client->getPublicClientId();
        if ($publicId === null) {
            return $client;
        }
        if (!$this->autodiscover->enabled() || $client->getType() != ApiClientType::BROKER()) {
            return null;
        }
        $clientData = $this->getPublicClient($publicId);
        if (!$clientData) {
            return null;
        }
        $this->applyPublicMetadata($client, $publicId, $clientData);
        $this->clientManager->updateClient($client);
        return $client;
    }

    private function provisionMappedPublicClient(string $clientId): ?ApiClient {
        if (!$this->autodiscover->enabled()) {
            return null;
        }
        try {
            $publicId = $this->autodiscover->getPublicIdBasedOnMappedId($clientId);
        } catch (\Throwable $e) {
            throw $this->autodiscoverUnavailable($e);
        }
        if (!$publicId) {
            return null;
        }
        $clientData = $this->getPublicClient($publicId);
        if (!$clientData) {
            return null;
        }

        // A prior request may have materialized this public application while
        // Autodiscover still received the original mapped id. Reuse that local
        // identity and repair/synchronize the credentials instead of creating
        // another ApiClient.
        $existingClient = $this->clientManager->findClientBy(['publicClientId' => $publicId]);
        if ($existingClient !== null) {
            if (!$existingClient instanceof ApiClient || $existingClient->getType() != ApiClientType::BROKER()) {
                return null;
            }
            $this->applyPublicMetadata($existingClient, $publicId, $clientData);
            $this->clientManager->updateClient($existingClient);
            $this->updateTargetCredentials($clientId, $existingClient);
            return $existingClient;
        }

        /** @var ApiClient $client */
        $client = $this->clientManager->createClient();
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $client->setType(ApiClientType::BROKER());
        $client->setPublicClientId($publicId);
        $this->applyPublicMetadata($client, $publicId, $clientData);
        $this->clientManager->updateClient($client);
        $this->updateTargetCredentials($clientId, $client);
        return $client;
    }

    /** @return array<string, mixed>|null */
    private function getPublicClient(string $publicId): ?array {
        try {
            $clientData = $this->autodiscover->getPublicClient($publicId);
        } catch (\Throwable $e) {
            throw $this->autodiscoverUnavailable($e);
        }
        if (!$clientData) {
            return null;
        }
        return $clientData;
    }

    /** @param array<string, mixed> $clientData */
    private function applyPublicMetadata(ApiClient $client, string $publicId, array $clientData): void {
        if (
            ($clientData['clientId'] ?? null) !== $publicId
            || !is_string($clientData['name'] ?? null)
            || !is_array($clientData['redirectUris'] ?? null)
            || !$clientData['redirectUris']
        ) {
            throw new ApiException('Autodiscover returned inconsistent public client metadata.', 503);
        }
        foreach ($clientData['redirectUris'] as $redirectUri) {
            if (!is_string($redirectUri)) {
                throw new ApiException('Autodiscover returned inconsistent public client metadata.', 503);
            }
        }
        $client->updateDataFromAutodiscover($clientData);
    }

    private function updateTargetCredentials(string $clientId, ApiClient $client): void {
        try {
            $this->autodiscover->updateTargetCloudCredentials($clientId, $client);
        } catch (\Throwable $e) {
            throw $this->autodiscoverUnavailable($e);
        }
    }

    private function autodiscoverUnavailable(\Throwable $previous): ApiException {
        return new ApiException('Autodiscover is unavailable.', 503, $previous instanceof \Exception ? $previous : null);
    }
}
