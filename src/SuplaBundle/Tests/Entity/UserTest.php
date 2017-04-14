<?php
namespace SuplaBundle\Tests\Entity;

use SuplaBundle\Entity\User;

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
}
