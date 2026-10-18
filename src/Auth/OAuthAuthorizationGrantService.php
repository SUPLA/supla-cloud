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
use App\Entity\Main\User;
use App\Model\Transactional;
use App\Repository\ApiClientAuthorizationRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

/** Shared consent persistence for the FOS browser listener and the internal API. */
class OAuthAuthorizationGrantService {
    use Transactional;

    public function __construct(private readonly ApiClientAuthorizationRepository $authorizationRepository) {
    }

    /**
     * The internal API supplies FOS completion so consent and code are committed
     * together. Legacy FOS events keep their original order and need no callback.
     * No synthetic FOS events, form fields or session access are needed by API callers.
     */
    public function authorize(User $user, ApiClient $client, string $scope, ?callable $issueCode = null): mixed {
        $mqttSettingsChanged = false;
        $result = $this->transactional(function (EntityManagerInterface $entityManager) use (
            $user,
            $client,
            $scope,
            $issueCode,
            &$mqttSettingsChanged
        ) {
            // Serialize first grants and scope unions, including requests for
            // different clients that both enable MQTT for the same user.
            $entityManager->refresh($user, LockMode::PESSIMISTIC_WRITE);
            $authorization = $this->authorizationRepository->findOneByUserAndApiClient($user, $client);
            if ($authorization) {
                $authorization->authorizeNewScope($scope);
                $entityManager->persist($authorization);
            } else {
                $authorization = $user->addApiClientAuthorization($client, $scope);
                $entityManager->persist($user);
            }
            if ((new OAuthScope($authorization->getScope()))->hasScope('mqtt_broker') && !$user->isMqttBrokerEnabled()) {
                $user->setMqttBrokerEnabled(true);
                $mqttSettingsChanged = true;
            }
            return $issueCode ? $issueCode() : null;
        });
        if ($mqttSettingsChanged) {
            $this->suplaServer->mqttSettingsChanged($user);
        }
        return $result;
    }
}
