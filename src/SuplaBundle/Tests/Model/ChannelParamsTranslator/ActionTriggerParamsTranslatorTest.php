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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsActionTrigger;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\ChannelParamsTranslator\ActionTriggerParamsTranslator;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Tests\Traits\ChannelStub;
use SuplaBundle\Tests\Traits\SceneMocks;
use SuplaBundle\Tests\Traits\UserMocks;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActionTriggerParamsTranslatorTest extends TestCase {
    use UserMocks;
    use SceneMocks;

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
        $this->configTranslator->setTokenStorage($this->getMockedTokenStorage());
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
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
            'HOLD' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE, 'params' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER());
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create()->setFunction(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getConfig()['actions']);
        return $channel;
    }

    /** @depends testCanSetValidActions */
    public function testGettingActionsInConfig(IODeviceChannel $channel) {
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('actions', $config);
        $this->assertCount(2, $config['actions']);
    }

    public function testFillsActionParamsIfMissing() {
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]],
            'PRESS_2X' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER());
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create()->setFunction(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        $actions['PRESS']['action']['params'] = [];
        $actions['PRESS_2X']['action']['params'] = [];
        $this->assertEquals($actions, $channel->getConfig()['actions']);
    }

    public function testCannotSetActionWithNoSubjectId() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = [['subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]]];
        $channel = new IODeviceChannel();
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithNoSubjectType() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = [['subjectId' => 1, 'action' => ['id' => ChannelFunctionAction::OPEN]]];
        $channel = new IODeviceChannel();
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithNoAction() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = [['subjectType' => 'channel', 'subjectId' => 1]];
        $channel = new IODeviceChannel();
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithInvalidSubject() {
        $this->expectException(NotFoundHttpException::class);
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]],
            'PRESS_2X' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER());
        $this->subjectRepositoryMock->method('findForUser')->willThrowException(new NotFoundHttpException());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCanNotSetActionThatDoesNotMatchFunction() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER());
        $this->subjectRepositoryMock->method('findForUser')->willReturnOnConsecutiveCalls(ChannelStub::create());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCanNotSetInvalidActionParams() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create()->setFunction(ChannelFunction::CONTROLLINGTHEDOORLOCK()));
        $this->actionExecutorMock->method('validateActionParams')->willThrowException(new \InvalidArgumentException());
        $this->configTranslator->setParamsFromConfig(ChannelStub::create(ChannelType::ACTION_TRIGGER()), ['actions' => $actions]);
    }

    public function testCannotSetInvalidTrigger() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('UNICORN trigger is not');
        $actions = [
            'UNICORN' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER());
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create()->setFunction(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetUnsupportedTrigger() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('HOLD trigger is not');
        $actions = [
            'HOLD' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->flags(ChannelFunctionBitsActionTrigger::PRESS);
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create()->setFunction(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testLowercaseTriggerNamesAreNotSupported() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('press trigger is not');
        $actions = [
            'press' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create()->setFunction(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig(ChannelStub::create(ChannelType::ACTION_TRIGGER()), ['actions' => $actions]);
    }
}
