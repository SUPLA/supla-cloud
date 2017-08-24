<?php

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SuplaBundle\Entity\Location;

class LocationsFixture extends SuplaFixture {
    const ORDER = UsersFixture::ORDER + 1;

    const LOCATION_BEDROOM = 'locationSypialnia';
    const LOCATION_OUTSIDE = 'locationNa zewnątrz';
    const LOCATION_GARAGE = 'locationGaraż';

    public function load(ObjectManager $manager) {
        $user = $this->getReference(UsersFixture::USER);
        foreach (['Sypialnia', 'Na zewnątrz', 'Garaż'] as $caption) {
            /** @var Location $location */
            $location = $this->container->get('location_manager')->createLocation($user);
            $location->setCaption($caption);
            $manager->persist($location);
            $this->setReference('location' . $caption, $location);
        }
        $manager->flush();
    }
}
