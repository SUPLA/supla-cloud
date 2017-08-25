<?php

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Model\AccessIdManager;

class AccessIdsFixture extends SuplaFixture {
    const ORDER = UsersFixture::ORDER + 1;

    const ACCESS_ID_COMMON = 'accessIdWspólny';
    const ACCESS_ID_CHILDREN = 'accessIdDzieci';

    public function load(ObjectManager $manager) {
        $user = $this->getReference(UsersFixture::USER);
        foreach (['Wspólny', 'Dzieci'] as $caption) {
            /** @var AccessID $accessId */
            $accessId = $this->container->get('accessid_manager')->createID($user);
            $accessId->setCaption($caption);
            $manager->persist($accessId);
            $this->setReference('accessId' . $caption, $accessId);
        }
        $manager->flush();
    }
}
