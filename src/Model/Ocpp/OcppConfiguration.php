<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Model\Ocpp;

use App\Entity\Main\OcppChargingStation;
use Assert\Assertion;

class OcppConfiguration {
    public function __construct(private string $ocppPublicUrl) {
        $this->ocppPublicUrl = rtrim(trim($ocppPublicUrl), '/');
        if ($this->ocppPublicUrl !== '') {
            $url = parse_url($this->ocppPublicUrl);
            Assertion::true(
                $url && ($url['scheme'] ?? '') === 'wss' && !empty($url['host'])
                    && !isset($url['user']) && !isset($url['pass'])
                    && !isset($url['query']) && !isset($url['fragment']) && empty($url['path']),
                'SUPLA_OCPP_PUBLIC_URL must be a WSS base URL without credentials, path, query or fragment.'
            );
        }
    }

    public function isEnabled(): bool {
        return $this->ocppPublicUrl !== '';
    }

    public function describe(OcppChargingStation $station): array {
        $identity = $station->getChargePointIdentity();
        return [
            'id' => $station->getId(),
            'iodeviceId' => $station->getIODevice()->getId(),
            'name' => $station->getIODevice()->getComment() ?: $station->getIODevice()->getName(),
            'locationId' => $station->getIODevice()->getLocation()->getId(),
            'protocol' => 'OCPP 1.6 JSON',
            'securityProfile' => 2,
            'baseUrl' => $this->isEnabled() ? $this->ocppPublicUrl : null,
            'url' => $this->isEnabled() ? $this->ocppPublicUrl . '/' . $identity : null,
            'chargePointIdentity' => $identity,
            'username' => $identity,
        ];
    }
}
