<?php

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class SuplaFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface {
    use ContainerAwareTrait;

    const ORDER = 0;

    public function getOrder() {
        return static::ORDER;
    }
}
