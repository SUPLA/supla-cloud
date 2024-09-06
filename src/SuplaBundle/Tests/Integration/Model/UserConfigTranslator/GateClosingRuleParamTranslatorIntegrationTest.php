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

namespace SuplaBundle\Tests\Integration\Model\UserConfigTranslator;

use SuplaBundle\Entity\Main\GateClosingRule;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\GateClosingRuleRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class GateClosingRuleParamTranslatorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var SubjectConfigTranslator */
    private $paramsTranslator;
    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var IODeviceChannel */
    private $gate;
    /** @var GateClosingRuleRepository */
    private $ruleRepository;

    public function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::SENSORNC, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $this->gate = $this->device->getChannels()[0];
        $this->ruleRepository = $this->getEntityManager()->getRepository(GateClosingRule::class);
    }

    /** @before */
    public function init() {
        $this->paramsTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
    }

    public function testNoRuleAtTheBeginning() {
        $this->assertNull($this->ruleRepository->find($this->gate->getId()));
        $config = $this->paramsTranslator->getConfig($this->gate);
        $this->assertArrayHasKey('closingRule', $config);
        $this->assertNotNull($config['closingRule']);
        $this->assertEmpty($config['closingRule']);
    }

    public function testSettingMaxTimeOpen() {
        $this->paramsTranslator->setConfig($this->gate, ['closingRule' => ['maxTimeOpen' => 600]]);
        $this->getEntityManager()->flush();
        $rule = $this->ruleRepository->find($this->gate->getId());
        $this->assertNotNull($rule);
        $this->assertFalse($rule->isEnabled());
        $this->assertEquals(600, $rule->getMaxTimeOpen());
        $this->assertNull($rule->getActiveFrom());
        $this->assertNull($rule->getActiveTo());
        $this->assertNull($rule->getActiveHours());
        $this->assertTrue($rule->isActiveNow());
        $config = $this->paramsTranslator->getConfig($this->gate);
        $this->assertArrayHasKey('closingRule', $config);
        $this->assertNotNull($config['closingRule']);
        $this->assertFalse($config['closingRule']['enabled']);
        $this->assertEquals(600, $config['closingRule']['maxTimeOpen']);
    }

    /** @depends testSettingMaxTimeOpen */
    public function testSettingActiveHours() {
        $this->paramsTranslator->setConfig($this->gate, ['closingRule' => ['activeHours' => [2 => [2, 3, 4]]]]);
        $this->getEntityManager()->flush();
        $rule = $this->ruleRepository->find($this->gate->getId());
        $this->assertNotNull($rule);
        $this->assertFalse($rule->isEnabled());
        $this->assertEquals(600, $rule->getMaxTimeOpen());
        $this->assertNotNull($rule->getActiveHours());
        $this->assertEquals([2 => [2, 3, 4]], $rule->getActiveHours());
    }

    /** @depends testSettingMaxTimeOpen */
    public function testSettingActiveFrom() {
        $this->paramsTranslator->setConfig($this->gate, ['closingRule' => ['activeFrom' => '2022-10-25T15:09:00+02:00']]);
        $this->getEntityManager()->flush();
        $rule = $this->ruleRepository->find($this->gate->getId());
        $this->assertNotNull($rule);
        $this->assertFalse($rule->isEnabled());
        $this->assertEquals(600, $rule->getMaxTimeOpen());
        $this->assertNotNull($rule->getActiveFrom());
        $this->assertEquals('2022-10-25T13:09:00+00:00', $rule->getActiveFrom()->format(\DateTime::ATOM));
    }

    /** @depends testSettingActiveFrom */
    public function testClearingActiveFrom() {
        $this->paramsTranslator->setConfig($this->gate, ['closingRule' => ['activeFrom' => null]]);
        $this->getEntityManager()->flush();
        $rule = $this->ruleRepository->find($this->gate->getId());
        $this->assertEquals(600, $rule->getMaxTimeOpen());
        $this->assertNull($rule->getActiveFrom());
    }

    /** @depends testSettingActiveFrom */
    public function testSettingActiveFromToInvalidDate() {
        $this->expectException(\InvalidArgumentException::class);
        $this->paramsTranslator->setConfig($this->gate, ['closingRule' => ['activeFrom' => 'unicorn']]);
    }

    /** @depends testSettingActiveFrom */
    public function testSettingActiveFromAfterActiveTo() {
        $this->expectException(\InvalidArgumentException::class);
        $this->paramsTranslator->setConfig($this->gate, ['closingRule' => [
            'activeFrom' => '2022-10-25T15:09:00+02:00',
            'activeTo' => '2022-10-24T15:09:00+02:00',
        ]]);
    }
}
