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

use SuplaBundle\Entity\Main\AuditEntry;
use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\UserIcon;
use SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class DatabaseV23MigrationTest extends DatabaseMigrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @before */
    public function prepare() {
        $this->loadDumpV23();
        $this->initialize();
    }

    public function testMigratedCorrectly() {
        $this->splittingEmAndIcVersion20191226160845();
        $this->migratingIpAddressesVersion20200124084227();
        $this->migrationOfMeasurementLogsVersion20200430113342();
        $this->migratingSchedulesVersion20210525104812();
        $this->assertMigratedDailyScheduleWithAsteriskVersion20211108120835();
    }

    /**
     * @see Version20191226160845
     * @see UpdateChannelConfigsInitializationCommand
     */
    private function splittingEmAndIcVersion20191226160845() {
        $this->assertChannelParamsConfigIsSet();
        $this->assertChannelParamsConfigDefault();
        $this->assertElectricityMeterFunctionIsMigrated();
        $this->assertMigratedChannelIconsForElectricityMeter();
    }

    private function assertChannelParamsConfigIsSet() {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 150);
        $paramsTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->assertNotEmpty($channel->getUserConfig());
        $this->assertEquals($paramsTranslator->getConfig($channel), $channel->getUserConfig());
    }

    private function assertChannelParamsConfigDefault() {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 151);
        $this->assertEquals(
            [
                'openingSensorChannelId' => null,
                'openingSensorSecondaryChannelId' => null,
                'relayTimeMs' => 500,
                'timeSettingAvailable' => true,
                'numberOfAttemptsToOpen' => 5,
                'numberOfAttemptsToClose' => 5,
                'stateVerificationMethodActive' => false,
                'closingRule' => [],
            ],
            array_diff_key($channel->getUserConfig(), ['googleHome' => '', 'alexa' => '']),
        );
    }

    private function assertElectricityMeterFunctionIsMigrated() {
        // 66 -> ELECTRICITY_METER / ELECTRICITY_METER
        // 67 -> IMPULSECOUNTER / ELECTRICITY_METER
        $electricityMeter = $this->getEntityManager()->find(IODeviceChannel::class, 66);
        $electricityMeterImpulseCounter = $this->getEntityManager()->find(IODeviceChannel::class, 67);
        $this->assertEquals($electricityMeter->getType()->getId(), ChannelType::ELECTRICITYMETER);
        $this->assertEquals($electricityMeterImpulseCounter->getType()->getId(), ChannelType::IMPULSECOUNTER);
        $this->assertEquals($electricityMeter->getFunction()->getId(), ChannelFunction::ELECTRICITYMETER);
        $this->assertEquals($electricityMeterImpulseCounter->getFunction()->getId(), ChannelFunction::IC_ELECTRICITYMETER);
    }

    private function assertMigratedChannelIconsForElectricityMeter() {
        $iconForEm = $this->getEntityManager()->find(UserIcon::class, 1);
        $this->assertEquals(ChannelFunction::ELECTRICITYMETER, $iconForEm->getFunction()->getId());
        $iconForEmImpulseCounter = $this->getEntityManager()->find(UserIcon::class, 2);
        $this->assertNotNull($iconForEmImpulseCounter);
        $this->assertEquals(ChannelFunction::IC_ELECTRICITYMETER, $iconForEmImpulseCounter->getFunction()->getId());
        $this->assertEquals($iconForEm->getUser()->getId(), $iconForEmImpulseCounter->getUser()->getId());
        $electricityMeter = $this->getEntityManager()->find(IODeviceChannel::class, 66);
        $electricityMeterImpulseCounter = $this->getEntityManager()->find(IODeviceChannel::class, 67);
        $this->assertEquals($iconForEm->getId(), $electricityMeter->getUserIcon()->getId());
        $this->assertEquals($iconForEmImpulseCounter->getId(), $electricityMeterImpulseCounter->getUserIcon()->getId());
    }

    /**
     * @see Version20200124084227
     */
    private function migratingIpAddressesVersion20200124084227() {
        $this->assertReadingDirectLinkIpAddressWithMysqlFunction();
        $this->assertReadingIoDeviceIpAddressesWithMysqlFunction();
        $this->assertReadingAuditEntryIpAddressWithMysqlFunction();
        $this->assertReadingClientAppIpAddressesWithMysqlFunction();
    }

    private function assertReadingDirectLinkIpAddressWithMysqlFunction() {
        $directLink = $this->getEntityManager()->find(DirectLink::class, 1);
        $this->assertEquals('217.99.244.137', $directLink->getLastIpv4());
    }

    private function assertReadingIoDeviceIpAddressesWithMysqlFunction() {
        $device = $this->getEntityManager()->find(IODevice::class, 1);
        $this->assertEquals('0.80.57.249', $device->getRegIpv4());
        $this->assertEquals('224.200.244.137', $device->getLastIpv4());
    }

    private function assertReadingAuditEntryIpAddressWithMysqlFunction() {
        $auditEntry = $this->getEntityManager()->find(AuditEntry::class, 1);
        $this->assertEquals('127.0.0.1', $auditEntry->getIpv4());
    }

    private function assertReadingClientAppIpAddressesWithMysqlFunction() {
        $clientApp = $this->getEntityManager()->find(ClientApp::class, 1);
        $this->assertEquals('254.250.244.237', $clientApp->getRegIpv4());
        $this->assertEquals('123.173.115.31', $clientApp->getLastAccessIpv4());
    }

    /**
     * @see Version20200430113342
     */
    private function migrationOfMeasurementLogsVersion20200430113342() {
        $this->assertHasLogItems();
        $this->assertRemovingDuplicates();
    }

    private function assertHasLogItems() {
        /** @var \SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem $log */
        $log = $this->getEntityManager('measurement_logs')->createQuery('SELECT t FROM ' . TemperatureLogItem::class . ' t')
            ->setMaxResults(1)
            ->getSingleResult();
        $this->assertEquals(2, $log->getChannelId());
        $this->assertEquals(38, $log->getTemperature());
    }

    private function assertRemovingDuplicates() {
        $logsCount = $this->getEntityManager('measurement_logs')
            ->createQuery('SELECT COUNT(t.channel_id) FROM ' . TemperatureLogItem::class . ' t')
            ->getSingleScalarResult();
        $this->assertEquals(2, $logsCount);
    }

    /**
     * @see Version20210525104812
     */
    private function migratingSchedulesVersion20210525104812() {
        $this->assertMigratedDailyScheduleWithAsterisk();
        $this->assertMigratedDailyScheduleWithDays();
        $this->assertMigratedHourlyScheduleToDaily();
        $this->assertMigratedMinutelyScheduleToDaily();
    }

    private function assertMigratedDailyScheduleWithAsterisk() {
        /** @var Schedule $schedule */
        $schedule = $this->getEntityManager()->find(Schedule::class, 1);
        $this->assertEquals(ScheduleMode::DAILY, $schedule->getMode()->getValue());
        $config = $schedule->getConfig();
        $this->assertNotNull($config);
        $this->assertCount(1, $config);
        $this->assertEquals('SS10 * * * *', $config[0]['crontab']);
        $this->assertEquals(['id' => 30, 'param' => null], $config[0]['action']);
    }

    private function assertMigratedDailyScheduleWithDays() {
        /** @var \SuplaBundle\Entity\Main\Schedule $schedule */
        $schedule = $this->getEntityManager()->find(Schedule::class, 6);
        $this->assertEquals(ScheduleMode::DAILY, $schedule->getMode()->getValue());
        $config = $schedule->getConfig();
        $this->assertNotNull($config);
        $this->assertCount(1, $config);
        $this->assertEquals('SR10 * * * 1,2,3', $config[0]['crontab']);
        $this->assertEquals(['id' => 80, 'param' => ['hue' => 196, 'color_brightness' => 20]], $config[0]['action']);
    }

    private function assertMigratedHourlyScheduleToDaily() {
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

    private function assertMigratedMinutelyScheduleToDaily() {
        /** @var \SuplaBundle\Entity\Main\Schedule $schedule */
        $schedule = $this->getEntityManager()->find(Schedule::class, 2);
        $this->assertEquals(ScheduleMode::MINUTELY, $schedule->getMode()->getValue());
        $config = $schedule->getConfig();
        $this->assertNotNull($config);
        $this->assertCount(1, $config);
        $this->assertEquals('*/10 * * * *', $config[0]['crontab']);
        $this->assertEquals(['id' => 30, 'param' => null], $config[0]['action']);
    }

    /**
     * @see Version20211108120835
     */
    private function assertMigratedDailyScheduleWithAsteriskVersion20211108120835() {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 67);
        $this->assertEquals(ChannelType::IMPULSECOUNTER, $channel->getType()->getId());
        $this->assertEquals(0, $channel->getParam1());
        $this->assertArrayHasKey('initialValue', $channel->getUserConfig());
    }
}
