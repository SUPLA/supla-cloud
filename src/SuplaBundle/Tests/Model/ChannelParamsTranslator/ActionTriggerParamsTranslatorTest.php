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

namespace SuplaBundle\Tests\Model\ChannelParamsTranslator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsActionTrigger;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\ChannelParamsTranslator\ActionTriggerParamsTranslator;
use SuplaBundle\Repository\ActionableSubjectRepository;

class ActionTriggerParamsTranslatorTest extends TestCase {
    /** @var ActionTriggerParamsTranslator */
    private $configTranslator;
    /** @var ActionableSubjectRepository|MockObject */
    private $subjectRepositoryMock;
    /** @var ChannelActionExecutor|MockObject */
    private $actionExecutorMock;

    /** @before */
    public function createTranslator() {
        $this->subjectRepositoryMock = $this->createMock(ActionableSubjectRepository::class);
        $this->actionExecutorMock = $this->createMock(ChannelActionExecutor::class);
        $this->configTranslator = new ActionTriggerParamsTranslator($this->subjectRepositoryMock, $this->actionExecutorMock);
    }

    public function testGettingSupportedTriggers() {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'flags', ChannelFunctionBitsActionTrigger::HOLD | ChannelFunctionBitsActionTrigger::PRESS_3X);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('supportedTriggers', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['supportedTriggers']);
    }

    public function testCloudCannotChangeSupportedTriggers() {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'flags', ChannelFunctionBitsActionTrigger::HOLD | ChannelFunctionBitsActionTrigger::PRESS_3X);
        $this->configTranslator->setParamsFromConfig($channel, ['supportedTriggers' => []]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('supportedTriggers', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['supportedTriggers']);
    }

    public function testCannotSetInvalidActionsSyntax() {
        $this->expectException(\InvalidArgumentException::class);
        $this->configTranslator->setParamsFromConfig(new IODeviceChannel(), ['actions' => null]);
    }

    public function testCanSetValidActions() {
        $actions = [
            ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]],
            ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE]],
        ];
        $channel = new IODeviceChannel();
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(new IODeviceChannel(), $this->createMock(Scene::class));
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getConfig()['actions']);
    }
}
