<?php

namespace SuplaBundle\Tests\Entity;

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\AnyFieldSetter;

class UserTest extends \PHPUnit_Framework_TestCase {
    public function testSettingDefaultTimezoneByDefault() {
        $user = new User();
        $this->assertEquals(date_default_timezone_get(), $user->getTimezone());
    }

    public function testSettingDefaultTimezoneIfBad() {
        $user = new User();
        $user->setTimezone('crazy');
        $this->assertEquals(date_default_timezone_get(), $user->getTimezone());
    }

    public function testSettingCustomTimezone() {
        $user = new User();
        $user->setTimezone('Europe/Skopje');
        $this->assertEquals('Europe/Skopje', $user->getTimezone());
    }

    public function testEnablingClientAppRegistration() {
        $user = new User();
        $now = new \DateTime();
        $user->enableClientsRegistration(600);
        $this->assertNotNull($user->getClientsRegistrationEnabled());
        $this->assertGreaterThanOrEqual($now->getTimestamp(), $user->getClientsRegistrationEnabled()->getTimestamp() - 600);
    }

    public function testDisablingClientAppRegistration() {
        $user = new User();
        $user->enableClientsRegistration(600);
        $user->disableClientsRegistration();
        $this->assertNull($user->getClientsRegistrationEnabled());
    }

    public function testReturnsClientsRegistrationNullIfTimeHasPassed() {
        $user = new User();
        AnyFieldSetter::set($user, 'clientsRegistrationEnabled', new \DateTime('-1second'));
        $this->assertNull($user->getClientsRegistrationEnabled());
    }
}
