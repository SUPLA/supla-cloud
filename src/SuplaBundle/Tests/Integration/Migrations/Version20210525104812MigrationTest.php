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

use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Enums\ScheduleMode;

/**
 * @see Version20210525104812
 */
class Version20210525104812MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV23();
        $this->initialize();
    }

    public function testMigratedDailyScheduleWithAsterisk() {
        /** @var Schedule $schedule */
        $schedule = $this->getEntityManager()->find(Schedule::class, 1);
        $this->assertEquals(ScheduleMode::DAILY, $schedule->getMode()->getValue());
        $config = $schedule->getConfig();
        $this->assertNotNull($config);
        $this->assertCount(1, $config);
        $this->assertEquals('SS10 * * * *', $config[0]['crontab']);
        $this->assertEquals(['id' => 30, 'param' => null], $config[0]['action']);
    }

    public function testMigratedDailyScheduleWithDays() {
        /** @var \SuplaBundle\Entity\Main\Schedule $schedule */
        $schedule = $this->getEntityManager()->find(Schedule::class, 6);
        $this->assertEquals(ScheduleMode::DAILY, $schedule->getMode()->getValue());
        $config = $schedule->getConfig();
        $this->assertNotNull($config);
        $this->assertCount(1, $config);
        $this->assertEquals('SR10 * * * 1,2,3', $config[0]['crontab']);
        $this->assertEquals(['id' => 80, 'param' => ['hue' => 196, 'color_brightness' => 20]], $config[0]['action']);
    }

    public function testMigratedHourlyScheduleToDaily() {
        /** @var Schedule $schedule */
        $schedule = $this->getEntityManager()->find(Schedule::class, 7);
        $this->assertEquals(ScheduleMode::DAILY, $schedule->getMode()->getValue());
        $config = $schedule->getConfig();
        $this->assertNotNull($config);
        $this->assertCount(2, $config);
        $this->assertEquals('15 14 * * *', $config[0]['crontab']);
        $this->assertEquals('15 19 * * *', $config[1]['crontab']);
        $this->assertEquals(['id' => 80, 'param' => ['hue' => 196, 'color_brightness' => 20]], $config[0]['action']);
        $this->assertEquals(['id' => 80, 'param' => ['hue' => 196, 'color_brightness' => 20]], $config[1]['action']);
    }

    public function testMigratedMinutelyScheduleToDaily() {
        /** @var \SuplaBundle\Entity\Main\Schedule $schedule */
        $schedule = $this->getEntityManager()->find(Schedule::class, 2);
        $this->assertEquals(ScheduleMode::MINUTELY, $schedule->getMode()->getValue());
        $config = $schedule->getConfig();
        $this->assertNotNull($config);
        $this->assertCount(1, $config);
        $this->assertEquals('*/10 * * * *', $config[0]['crontab']);
        $this->assertEquals(['id' => 30, 'param' => null], $config[0]['action']);
    }
}
