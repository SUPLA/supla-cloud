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

namespace SuplaBundle\Tests\Integration\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\GateClosingRule;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Repository\GateClosingRuleRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class GateClosingRuleParamTranslatorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelParamConfigTranslator */
    private $paramsTranslator;
    /** @var User */
    private $user;
    /** @var IODeviceChannel */
    private $gate;
    /** @var GateClosingRuleRepository */
    private $ruleRepository;

    public function initializeDatabaseForTests() {
        $this->initializeDatabaseWithMigrations();
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
        $this->paramsTranslator = self::$container->get(ChannelParamConfigTranslator::class);
        $this->simulateAuthentication($this->user);
    }

    public function testNoRuleAtTheBeginning() {
        $this->assertNull($this->ruleRepository->find($this->gate->getId()));
        $config = $this->paramsTranslator->getConfigFromParams($this->gate);
        $this->assertArrayHasKey('closingRule', $config);
        $this->assertNull($config['closingRule']);
    }

    public function testSettingMaxTimeOpen() {
        $this->paramsTranslator->setParamsFromConfig($this->gate, ['closingRule' => ['maxTimeOpen' => 10]]);
        $this->getEntityManager()->flush();
        $rule = $this->ruleRepository->find($this->gate->getId());
        $this->assertNotNull($rule);
        $this->assertFalse($rule->isEnabled());
        $this->assertEquals(10, $rule->getMaxTimeOpen());
        $this->assertNull($rule->getActiveFrom());
        $this->assertNull($rule->getActiveTo());
        $this->assertNull($rule->getActiveHours());
        $this->assertTrue($rule->isActiveNow());
        $config = $this->paramsTranslator->getConfigFromParams($this->gate);
        $this->assertArrayHasKey('closingRule', $config);
        $this->assertNotNull($config['closingRule']);
        $this->assertFalse($config['closingRule']['enabled']);
        $this->assertEquals(10, $config['closingRule']['maxTimeOpen']);
    }

    /** @depends testSettingMaxTimeOpen */
    public function testSettingActiveHours() {
        $this->paramsTranslator->setParamsFromConfig($this->gate, ['closingRule' => ['activeHours' => [2 => [2, 3, 4]]]]);
        $this->getEntityManager()->flush();
        $rule = $this->ruleRepository->find($this->gate->getId());
        $this->assertNotNull($rule);
        $this->assertFalse($rule->isEnabled());
        $this->assertEquals(10, $rule->getMaxTimeOpen());
        $this->assertNotNull($rule->getActiveHours());
        $this->assertEquals([2 => [2, 3, 4]], $rule->getActiveHours());
    }
}
