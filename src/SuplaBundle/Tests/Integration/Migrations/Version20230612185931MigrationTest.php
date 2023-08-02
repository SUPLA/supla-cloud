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

namespace SuplaBundle\Tests\Integration\Migrations;

use Supla\Migrations\Version20230612185931;
use SuplaBundle\Entity\Main\SettingsString;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Repository\SettingsStringRepository;

/**
 * @see Version20230612185931
 */
class Version20230612185931MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV2207();
        file_put_contents(Version20230612185931::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH, 'the_token_for_test');
        $this->initialize();
    }

    public function testSettingSaved() {
        /** @var SettingsStringRepository $repository */
        $repository = $this->getDoctrine()->getRepository(SettingsString::class);
        $this->assertTrue($repository->hasValue(InstanceSettings::TARGET_TOKEN));
        $this->assertEquals('the_token_for_test', $repository->getValue(InstanceSettings::TARGET_TOKEN));
        $this->assertFileNotExists(Version20230612185931::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH);
    }
}
