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

namespace SuplaBundle\Auth\Voter;

use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BrokerRequestSecurityVoter extends Voter {
    const PERMISSION_NAME = 'isRequestFromBroker';

    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(SuplaAutodiscover $autodiscover) {
        $this->autodiscover = $autodiscover;
    }

    /** @inheritdoc */
    protected function supports($attribute, $subject) {
        return $attribute == self::PERMISSION_NAME;
    }

    /**
     * @param Request $request
     * @inheritdoc
     */
    protected function voteOnAttribute($attribute, $request, TokenInterface $token) {
        if ($request instanceof Request) {
            return $this->isRequestFromBroker($request);
        }
        return false;
    }

    public function isRequestFromBroker(Request $request): bool {
        if ($request->headers->has('SUPLA-Broker-Token')) {
            $brokerToken = $request->headers->get('SUPLA-Broker-Token');
            $info = $this->autodiscover->getInfo($brokerToken);
            return boolval($info['isBroker'] ?? false);
        } else {
            return false;
        }
    }
}
