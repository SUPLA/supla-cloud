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

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Model\AccessIdManager;

class AccessIdsFixture extends SuplaFixture {
    const ORDER = UsersFixture::ORDER + 1;

    const ACCESS_ID_COMMON = 'accessIdWspólny';
    const ACCESS_ID_CHILDREN = 'accessIdDzieci';
    const ACCESS_ID_SUPLER = 'accessIdSupler';

    /** @var AccessIdManager */
    private $accessIdManager;

    public function __construct(AccessIdManager $accessIdManager) {
        $this->accessIdManager = $accessIdManager;
    }

    public function load(ObjectManager $manager) {
        $user = $this->getReference(UsersFixture::USER);
        foreach (['Wspólny', 'Dzieci'] as $caption) {
            /** @var AccessID $accessId */
            $accessId = $this->accessIdManager->createID($user);
            $accessId->setCaption($caption);
            $manager->persist($accessId);
            $this->setReference('accessId' . $caption, $accessId);
        }
        $accessId = $this->accessIdManager->createID($this->getReference(UsersFixture::USER2));
        $accessId->setCaption('Supler Access ID');
        $manager->persist($accessId);
        $this->setReference(self::ACCESS_ID_SUPLER, $accessId);
        $manager->flush();
    }
}
