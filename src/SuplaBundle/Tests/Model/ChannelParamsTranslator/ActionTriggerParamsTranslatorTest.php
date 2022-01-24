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
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
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
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['HOLD', 'PRESS_3X']]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('actionTriggerCapabilities', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['actionTriggerCapabilities']);
    }

    public function testCloudCannotChangeSupportedTriggers() {
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['HOLD', 'PRESS_3X']]);
        $this->configTranslator->setParamsFromConfig($channel, ['actionTriggerCapabilities' => ['PRESS_2X']]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('actionTriggerCapabilities', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['actionTriggerCapabilities']);
    }

    public function testCanSetEmptyActions() {
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER());
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => null]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('actions', $config);
        $this->assertEmpty($config['actions']);
    }

    public function testCanSetValidActions() {
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'param' => []]],
            'HOLD' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE, 'param' => []]],
        ];
        $channel = ChannelStub::create(ChannelFunction::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['HOLD', 'PRESS']]);
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
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
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS_2X', 'PRESS']]);
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        $actions['PRESS']['action']['param'] = [];
        $actions['PRESS_2X']['action']['param'] = [];
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testCanSetGenericAction() {
        $actions = ['PRESS' => [
            'subjectType' => ActionableSubjectType::OTHER,
            'action' => ['id' => ChannelFunctionAction::GENERIC, 'param' => ['action' => 'disableLocalFunction']],
        ]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testCanSetCopyChannelState() {
        $actions = ['PRESS' => [
            'subjectType' => ActionableSubjectType::CHANNEL,
            'subjectId' => 1,
            'action' => [
                'id' => ChannelFunctionAction::COPY,
                'param' => ['sourceChannelId' => 2],
            ],
        ]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->subjectRepositoryMock->method('findForUser')->willReturn(ChannelStub::create(ChannelFunction::POWERSWITCH()));
        $this->actionExecutorMock->method('validateActionParams')->willReturnArgument(2);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testCannotSetActionWithNoSubjectId() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = ['PRESS' => ['subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithNoSubjectType() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = ['PRESS' => ['subjectId' => 1, 'action' => ['id' => ChannelFunctionAction::OPEN]]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithNoAction() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = ['PRESS' => ['subjectType' => 'channel', 'subjectId' => 1]];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())->properties(['actionTriggerCapabilities' => ['PRESS']]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetActionWithInvalidSubject() {
        $this->expectException(NotFoundHttpException::class);
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN]],
            'PRESS_2X' => ['subjectId' => 1, 'subjectType' => 'scene', 'action' => ['id' => ChannelFunctionAction::EXECUTE]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectRepositoryMock->method('findForUser')->willThrowException(new NotFoundHttpException());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCanNotSetActionThatDoesNotMatchFunction() {
        $this->expectException(\InvalidArgumentException::class);
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
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
            ->willReturnOnConsecutiveCalls(ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()));
        $this->actionExecutorMock->method('validateActionParams')->willThrowException(new \InvalidArgumentException());
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSetUnsupportedTrigger() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('HOLD trigger is not');
        $actions = [
            'HOLD' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'params' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testLowercaseTriggerNamesAreNotSupported() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('press trigger is not');
        $actions = [
            'press' => ['subjectId' => 1, 'subjectType' => 'channel', 'action' => ['id' => ChannelFunctionAction::OPEN, 'param' => []]],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
    }

    public function testCannotSaveRubbishInConfig() {
        $actions = [
            'PRESS' => ['subjectId' => 1, 'subjectType' => 'channel', 'unicorn' => 'flower',
                'action' => ['id' => ChannelFunctionAction::OPEN, 'param' => [], 'unicorn' => 'flower']],
        ];
        $channel = ChannelStub::create(ChannelType::ACTION_TRIGGER())
            ->properties(['actionTriggerCapabilities' => ['PRESS', 'PRESS_2X']]);
        $this->subjectRepositoryMock->method('findForUser')
            ->willReturnOnConsecutiveCalls(ChannelStub::create(ChannelFunction::CONTROLLINGTHEDOORLOCK()), $this->createSceneMock());
        $this->actionExecutorMock->method('validateActionParams')->willReturn([]);
        $this->configTranslator->setParamsFromConfig($channel, ['actions' => $actions]);
        unset($actions['PRESS']['unicorn']);
        unset($actions['PRESS']['action']['unicorn']);
        $this->assertEquals($actions, $channel->getUserConfig()['actions']);
    }

    public function testGettingRelatedChannelIdNull() {
        $channel = new IODeviceChannel();
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('relatedChannelId', $config);
        $this->assertArrayHasKey('hideInChannelsList', $config);
        $this->assertNull($config['relatedChannelId']);
        $this->assertFalse($config['hideInChannelsList']);
    }

    public function testGettingRelatedChannelId() {
        $channel = new IODeviceChannel();
        $channel->setParam1(123);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('relatedChannelId', $config);
        $this->assertArrayHasKey('hideInChannelsList', $config);
        $this->assertEquals(123, $config['relatedChannelId']);
        $this->assertTrue($config['hideInChannelsList']);
    }

    public function testCloudCannotChangeRelatedChannelId() {
        $channel = new IODeviceChannel();
        $channel->setParam1(123);
        $this->configTranslator->setParamsFromConfig($channel, ['relatedChannelId' => 234]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('relatedChannelId', $config);
        $this->assertEquals(123, $config['relatedChannelId']);
    }
}
