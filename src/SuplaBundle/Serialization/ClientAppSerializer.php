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

namespace SuplaBundle\Serialization;

use Assert\Assertion;
use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Supla\SuplaServerAware;

class ClientAppSerializer extends AbstractSerializer {
    use SuplaServerAware;
    use CurrentUserAware;

    /**
     * @param \SuplaBundle\Entity\Main\ClientApp $clientApp
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $clientApp, array $context) {
        $normalized['accessIdId'] = $clientApp->getAccessId() ? $clientApp->getAccessId()->getId() : null;
        if ($this->isSerializationGroupRequested('connected', $context)) {
            $normalized['connected'] = $this->isClientAppConnected($clientApp);
        }
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            $normalized['regIpv4'] = ($normalized['regIpv4'] ?? null) ? ip2long($normalized['regIpv4']) : null;
            $normalized['lastAccessIpv4'] = ($normalized['lastAccessIpv4'] ?? null) ? ip2long($normalized['lastAccessIpv4']) : null;
        }
    }

    private function isClientAppConnected(ClientApp $clientApp): bool {
        if (!$clientApp->getEnabled()) {
            return false;
        }
        $user = $this->getCurrentUser();
        Assertion::notNull($user, 'User not authenticated');
        return $this->suplaServer->isClientAppConnected($clientApp);
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof ClientApp;
    }
}
