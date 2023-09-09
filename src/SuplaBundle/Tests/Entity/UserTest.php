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

namespace SuplaBundle\Tests\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitRule;
use SuplaBundle\Tests\AnyFieldSetter;

class UserTest extends TestCase {
    public function testSettingDefaultTimezoneByDefault() {
        $user = new User();
        $this->assertEquals(date_default_timezone_get(), $user->getTimezone());
    }

    public function testSettingDefaultTimezoneIfBad() {
        $user = new User();
        $user->setTimezone('crazy');
        $this->assertEquals(date_default_timezone_get(), $user->getTimezone());
    }

    public function testSettingDefaultTimezoneIfSupportedButNoLocation() {
        $user = new User();
        $user->setTimezone('CET');
        $this->assertEquals('Europe/Warsaw', $user->getTimezone());
    }

    public function testSettingCustomTimezone() {
        $user = new \SuplaBundle\Entity\Main\User();
        $user->setTimezone('Europe/Skopje');
        $this->assertEquals('Europe/Skopje', $user->getTimezone());
    }

    public function testEnablingClientAppRegistration() {
        $user = new User();
        $now = new DateTime();
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
        AnyFieldSetter::set($user, 'clientsRegistrationEnabled', new DateTime('-1second'));
        $this->assertNull($user->getClientsRegistrationEnabled());
    }

    public function testSettingApiRateLimit() {
        $user = new \SuplaBundle\Entity\Main\User();
        $this->assertNull($user->getApiRateLimit());
        $user->setApiRateLimit(new ApiRateLimitRule('5/10'));
        $this->assertEquals(new ApiRateLimitRule('5/10'), $user->getApiRateLimit());
        $user->setApiRateLimit(null);
        $this->assertNull($user->getApiRateLimit());
    }
}
