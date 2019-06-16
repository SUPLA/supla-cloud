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
use SuplaBundle\Entity\Location;
use SuplaBundle\Model\LocationManager;

class LocationsFixture extends SuplaFixture {
    const ORDER = UsersFixture::ORDER + 1;

    const LOCATION_BEDROOM = 'locationSypialnia';
    const LOCATION_OUTSIDE = 'locationNa zewnątrz';
    const LOCATION_GARAGE = 'locationGaraż';
    const LOCATION_SUPLER = 'locationSupler';

    /** @var LocationManager */
    private $locationManager;

    public function __construct(LocationManager $locationManager) {
        $this->locationManager = $locationManager;
    }

    public function load(ObjectManager $manager) {
        $user = $this->getReference(UsersFixture::USER);
        foreach (['Sypialnia', 'Na zewnątrz', 'Garaż'] as $caption) {
            /** @var Location $location */
            $location = $this->locationManager->createLocation($user);
            $location->setCaption($caption);
            $manager->persist($location);
            $this->setReference('location' . $caption, $location);
        }
        $location = $this->locationManager->createLocation($this->getReference(UsersFixture::USER2));
        $location->setCaption('Supler\'s location');
        $manager->persist($location);
        $this->setReference(self::LOCATION_SUPLER, $location);
        $manager->flush();
    }
}
