<?php

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Tests\AnyFieldSetter;

class ClientAppsFixture extends SuplaFixture {
    const ORDER = AccessIdsFixture::ORDER + 1;

    public function load(ObjectManager $manager) {
        $user = $this->getReference(UsersFixture::USER);
        $accessIds = [null, $this->getReference(AccessIdsFixture::ACCESS_ID_CHILDREN), $this->getReference(AccessIdsFixture::ACCESS_ID_COMMON)];
        foreach (['HTC One M8', 'iPhone 6s', 'Nokia 3310', 'Samsung Galaxy Tab S2', 'Apple iPad'] as $name) {
            $clientApp = new ClientApp();
            AnyFieldSetter::set($clientApp, [
                'guid' => rand(0, 9999999),
                'regDate' => new \DateTime('-' . rand(86400 * 7, 86400 * 60) . 'seconds'),
                'regIpv4' => rand(1, PHP_INT_MAX),
                'lastAccessDate' => new \DateTime('-' . rand(86400, 86400 * 7) . 'seconds'),
                'lastAccessIpv4' => rand(1, PHP_INT_MAX),
                'softwareVersion' => '1.' . rand(1, 100),
                'protocolVersion' => rand(1, 100),
                'user' => $user,
                'name' => $name,
                'enabled' => rand() % 2,
                'accessId' => $accessIds[rand(0, count($accessIds) - 1)],
            ]);
            $manager->persist($clientApp);
        }
        $manager->flush();
    }
}
