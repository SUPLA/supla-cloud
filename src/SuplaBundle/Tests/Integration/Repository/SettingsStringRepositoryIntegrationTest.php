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

namespace SuplaBundle\Tests\Integration\Repository;

use SuplaBundle\Entity\Main\SettingsString;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Repository\SettingsStringRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

/** @small */
class SettingsStringRepositoryIntegrationTest extends IntegrationTestCase {
    /** @var SettingsStringRepository */
    private $repository;

    protected function initializeDatabaseForTests() {
        $this->repository = $this->getDoctrine()->getRepository(SettingsString::class);
    }

    public function testUpdatingSettings() {
        $this->repository->setValue(InstanceSettings::TARGET_TOKEN, 'snail');
        $this->assertEquals('snail', $this->repository->getValue(InstanceSettings::TARGET_TOKEN));
        $this->repository->setValue(InstanceSettings::TARGET_TOKEN, 'rainbow');
        $this->assertEquals('rainbow', $this->repository->getValue(InstanceSettings::TARGET_TOKEN));
    }

    /** @depends testUpdatingSettings */
    public function testCheckingForSettingsExist() {
        $this->assertFalse($this->repository->hasValue(InstanceSettings::ALLOW_TGE_REPORTS));
        $this->assertTrue($this->repository->hasValue(InstanceSettings::TARGET_TOKEN));
    }
}
