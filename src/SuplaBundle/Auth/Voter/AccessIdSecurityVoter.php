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

use Assert\Assertion;
use SuplaBundle\Auth\Token\AccessIdAwareToken;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\HasLocation;
use SuplaBundle\Entity\Main\Location;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccessIdSecurityVoter extends Voter {
    const PERMISSION_NAME = 'accessIdContains';

    /** @inheritdoc */
    protected function supports($attribute, $subject) {
        return $attribute == self::PERMISSION_NAME;
    }

    /** @inheritdoc */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if ($token instanceof AccessIdAwareToken) {
            /** @var Location $location */
            $location = $subject;
            if ($subject instanceof HasLocation) {
                $location = $subject->getLocation();
            }
            Assertion::isInstanceOf($location, Location::class, 'Invalid voter subject: ' . get_class($subject));
            $accessId = $token->getAccessId();
            return in_array($location->getId(), EntityUtils::mapToIds($accessId->getLocations()));
        }
        return true;
    }
}
